<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App;
use Illuminate\Support\Facades\Lang;

class CrudController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function index($localecode, $modelo, Request $request) {
        App::setLocale($localecode);
        $modeloM = ucfirst($modelo);
        $config = CrudGenerator::getConfig($modelo);
        //return "<pre>" . print_r($config, true) . "</pre>";
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config));
    }

    public function create($localecode, $modelo, Request $request) {
        App::setLocale($localecode);
        $modeloM = ucfirst($modelo);
        $config = CrudGenerator::getConfig($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config));
    }

    public function store($localecode, $modelo, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = CrudGenerator::checkPermission($config)) {
            return $this->devolver($request, $config, $permiso);
        }
        if (($validator = CrudGenerator::validateModel($config, $request)) !== false) {
            if ($validator->fails()) {
                return $this->devolverValidation($validator, $modelo, $request);
            }
        }
        $objeto = CrudGenerator::saveObjeto($config, $request);
        return $this->devolver($request, $config, $permiso, $objeto);
    }

    public function show($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$registro = $modeloM::find($id);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        $config = CrudGenerator::getConfig($modelo);
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config, $registro), $registro);
    }

    public function edit($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        $config = CrudGenerator::getConfig($modelo);
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config, $registro), $registro);
    }

    public function update($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = CrudGenerator::checkPermission($config, $registro)) {
            return $this->devolver($request, $config, $permiso, $registro);
        }
        if (($validator = CrudGenerator::validateModel($config, $request)) !== false) {
            if ($validator->fails()) {
                return $this->devolverValidation($validator, $modelo, $request, $registro);
            }
        }
        $objeto = CrudGenerator::saveObjeto($config, $request, $registro);
        return $this->devolver($request, $config, $permiso, $objeto);
    }

    public function destroy($localecode, $modelo, $registro, Request $request) {
        App::setLocale($localecode);

        $modeloM = ucfirst($modelo);
        //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modeloM));
        //$config = CrudGenerator::translateConfig($config);
        $config = CrudGenerator::getConfig($modelo);
        if (!$permiso = CrudGenerator::checkPermission($config, $registro)) {
            return $this->devolver($request, $config, $permiso, $registro);
        }
        $muerto = $config['modelo']::find($registro);
        $datos = [
            'id' => $muerto->{$config['id']},
            'nombre' => $muerto->{$config['nombre']}
        ];
        $muerto->delete();
        return $this->devolver($request, $config, $permiso, 0, $datos);
    }

    private function devolverValidation($validator, $modelo, Request $request, $registro = 0) {
        $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
        $localecode = App::getLocale();

        $tipoReturn = "content";
        if ($request->has('_return')) {
            if (strtolower($request->_return) == 'purejson') {
                $tipoReturn = "json";
            } elseif (strtolower($request->_return) == 'modal') {
                $tipoReturn = "modal";
            } elseif (strtolower($request->_return) == 'simple') {
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
            /*
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
             * 
             */
            return back()->withInput()->withErrors($validator);
        }
    }

    private function devolver(Request $request, $config, $permiso, $objeto = 0, $extraDatos = "") {
        $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
        $localecode = App::getLocale();
        $modelo = strtolower(class_basename($config["modelo"]));
        $plural = $modelo . 's';
        $modeloM = ucfirst($modelo);
        $mensajes = [];
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
            if (strtolower($request->_return) == 'purejson') {
                $tipoReturn = "json";
            } elseif (strtolower($request->_return) == 'modal') {
                $tipoReturn = "modal";
            } elseif (strtolower($request->_return) == 'simple') {
                $tipoReturn = "simple";
            }
        }
        $titulo = "";
        switch ($action) {
            case "destroy":
                $titulo = trans('crudgenerator::admin.layout.borrar');
            case "show":
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.ver');
                }
            case "edit":
            case "update":
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.editar');
                }
            case "create":
            case "store":
                if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
                    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
                } else {
                    $singulares = $modelo;
                }
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.crear');
                }
                $titulo .= " " . ucfirst($singulares);
                break;
            case "index":
                if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.plural")) {
                    $titulo = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
                } else {
                    $titulo = $plural;
                }
                break;
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
                        if ($registro > 0) {
                            $mensajeStr = str_replace([":modelName", ":modelId"], [$objeto->{$config['nombre']}, $objeto->{$config['id']}], $mensajes[$action . "_success"]);
                        } else {
                            if (!is_array($extraDatos)) {
                                $mensajeStr = str_replace(":modelName", $extraDatos, $mensajes[$action . "_success"]);
                            } else {
                                $mensajeStr = str_replace([":modelName", ":modelId"], [$extraDatos['nombre'], $extraDatos['id']], $mensajes[$action . "_success"]);
                            }
                        }
                        $result = [
                            config("sirgrimorum.crudgenerator.status_messages_key") => $mensajeStr
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
                                ])->render();
                        break;
                    case "show":
                    case "edit":
                        $result = view('sirgrimorum::admin/' . $action . "." . $tipoReturn, [
                            "modelo" => $modeloM,
                            "base_url" => route('sirgrimorum_home', $localecode),
                            "plural" => $plural,
                            "registro" => $registro,
                            "config" => $config,
                                ])->render();
                        break;
                    case "store":
                    case "update":
                    case "destroy":
                        if ($registro > 0) {
                            $mensajeStr = str_replace([":modelName", ":modelId"], [$objeto->{$config['nombre']}, $objeto->{$config['id']}], $mensajes[$action . "_success"]);
                        } else {
                            if (!is_array($extraDatos)) {
                                $mensajeStr = str_replace(":modelName", $extraDatos, $mensajes[$action . "_success"]);
                            } else {
                                $mensajeStr = str_replace([":modelName", ":modelId"], [$extraDatos['nombre'], $extraDatos['id']], $mensajes[$action . "_success"]);
                            }
                        }
                        if ($tipoReturn == "content") {
                            $getReturn = "";
                        } else {
                            $getReturn = "?_return=" . $tipoReturn;
                        }
                        if ($tipoReturn == "content") {
                            $result = redirect(route('sirgrimorum_modelos::index', [
                                        'modelo' => $modelo,
                                        'localecode' => $localecode,
                                    ]) . $getReturn)->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensajeStr)
                            ;
                        } else {
                            $result = back()->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensajeStr);
                        }
                        break;
                }
            }
            if ($request->ajax() || $tipoReturn == 'json') {
                $result = [
                    'status' => 200,
                    'statusText' => trans("crudgenerator::admin.messages.200"),
                    'title' => $titulo,
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
                    'title' => $titulo,
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

    

}
