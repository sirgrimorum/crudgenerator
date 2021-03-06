<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Sirgrimorum\CrudGenerator\Exceptions\PreparingFileException;
use Sirgrimorum\CrudGenerator\Exceptions\PreparingFileForModelException;

class CrudController extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    public function index($modelo, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config));
    }

    public function create($modelo, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config));
    }

    public function store($modelo, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
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

    public function show($modelo, $registro, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        $registroDevolver = $registro;
        if (isset($config["query"]) && is_object($registro)) {
            if (is_callable($config['query'])) {
                $auxQuery = $config['query']();
            } else {
                $auxQuery = $config['query'];
            }
            if ($auxQuery instanceof Builder  || $auxQuery instanceof Collection || is_array($auxQuery)) {
                $registroDevolver = $registro->{$config['id']};
            }
        }
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config, $registro), $registroDevolver);
    }

    public function edit($modelo, $registro, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        return $this->devolver($request, $config, CrudGenerator::checkPermission($config, $registro), $registro);
    }

    public function update($modelo, $registro, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
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

    public function destroy($modelo, $registro, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        if (!$permiso = CrudGenerator::checkPermission($config, $registro)) {
            return $this->devolver($request, $config, $permiso, $registro);
        }
        $muerto = $config['modelo']::find($registro);
        $datos = [
            'id' => $muerto->{$config['id']},
            'nombre' => $muerto->{$config['nombre']}
        ];
        $modelClass = config('sirgrimorum.transarticles.default_articles_model');
        if (class_exists($modelClass)) {
            $findArticles = config('sirgrimorum.transarticles.default_findarticles_function_name');
            foreach ($config['campos'] as $campo => $detalles) {
                if ($detalles['tipo'] == "article") {
                    $segments = explode(".", $muerto->{$campo});
                    $scope = array_shift($segments);
                    $nickname = implode(".", $segments);
                    $deletedArticles = $modelClass::where("scope", "=", $scope)->where("nickname", "=", $nickname)->delete();
                }
            }
        }
        $muerto->delete();
        return $this->devolver($request, $config, $permiso, 0, $datos);
    }

    public function modelfile($modelo, $registro, $campo, Request $request)
    {
        $config = CrudGenerator::getConfigWithParametros($modelo);
        if (!$permiso = CrudGenerator::checkPermission($config, 0, 'show')) {
            return $this->devolver($request, $config, $permiso, 0, "", 'show');
        }
        if (isset($config['campos'][$campo])) {
            $detalles = $config['campos'][$campo];
            if ($request->has('_f')) {
                $filename = $request->_f;
                return $this->devolverFile($filename, $registro, $detalles);
            } else {
                throw new PreparingFileForModelException($modelo);
            }
        }
        throw new PreparingFileForModelException($modelo);
    }

    public function file(Request $request)
    {
        if ($request->has('_f')) {
            $filename = $request->_f;
            return $this->devolverFile($filename);
        }
        throw new PreparingFileException("");
    }

    private function devolverFile($filename, $id = null, $detalles = [])
    {
        $tipo = CrudGenerator::filenameIs($filename, $detalles);
        if (isset($detalles['showPath']) && is_callable($detalles['showPath'])){
            return $detalles['showPath']($id, $filename, $tipo, $detalles);
        } else {
            $path = CrudGenerator::getDisk($detalles)->url($filename);
        }
        switch ($tipo) {
            case 'video':
                $stream = new VideoStream($path);
                return response()->stream(function () use ($stream) {
                    $stream->start();
                });
                break;
            case 'audio':
                $stream = new AudioStream($path);
                return response()->stream(function () use ($stream) {
                    $stream->start();
                });
                /* return response()->stream(function () use ($file_location) {
                  $stream = fopen($file_location, 'r');
                  fpassthru($stream);
                  }, 200, $headers); */
                break;
            case 'image':
            case 'pdf':
            case 'text':
                return CrudGenerator::getDisk($detalles)->response($filename);
                break;
            default:
                return CrudGenerator::getDisk($detalles)->download($filename);
                break;
        }
    }

    private function devolverValidation($validator, $modelo, Request $request, $registro = 0)
    {
        //$action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);

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
              $extra = ['modelo' => $modelo];
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

    private function devolver(Request $request, $config, $permiso, $objeto = 0, $extraDatos = "", $action = "")
    {
        if ($action == "") {
            $action = substr($request->route()->getName(), stripos($request->route()->getName(), "::") + 2);
        }
        $modelo = strtolower(class_basename($config["modelo"]));
        //$plural = $modelo . 's';
        $plural = Str::plural($modelo);
        $modeloM = ucfirst($modelo);
        if (!is_object($objeto)) {
            $registro = $objeto;
        } else {
            $registro = $objeto->{$config['id']};
        }
        $mensajes = [];
        if (is_array(trans("crudgenerator::admin.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::admin.messages"));
        }
        if (is_array(trans("crudgenerator::' . strtolower($modelo) . '.messages"))) {
            $mensajes = array_merge($mensajes, trans("crudgenerator::' . strtolower($modelo) . '.messages"));
        }
        $tipoReturn = "content";
        if ($request->has('_return')) {
            if (strtolower($request->_return) == 'purejson' || strtolower($request->_return) == 'datatablesjson') {
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
                $titulo = trans('crudgenerator::admin.layout.labels.remove');
            case "show":
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.labels.show');
                }
            case "edit":
            case "update":
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.labels.edit');
                }
            case "create":
            case "store":
                if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
                    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
                } else {
                    $singulares = $modelo;
                }
                if ($titulo == "") {
                    $titulo = trans('crudgenerator::admin.layout.labels.create');
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
                        if (strtolower($request->_return) == 'datatablesjson') {
                            $result = CrudGenerator::lists_array($config, null, 'todo', $request);
                        } else {
                            $result = CrudGenerator::lists_array($config, null, 'complete');
                        }
                        break;
                    case "show":
                    case "edit":
                        $result = CrudGenerator::registry_array($config, $objeto, 'complete');
                        break;
                    case "store":
                    case "update":
                    case "destroy":
                        if ($registro > 0) {
                            if (!is_object($objeto)) {
                                $objeto = $config["modelo"]::find($registro);
                            }
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
                            "base_url" => route('sirgrimorum_home'),
                            "plural" => $plural,
                            "config" => $config,
                        ])->render();
                        break;
                    case "show":
                    case "edit":
                        $result = view('sirgrimorum::admin/' . $action . "." . $tipoReturn, [
                            "modelo" => $modeloM,
                            "base_url" => route('sirgrimorum_home'),
                            "plural" => $plural,
                            "registro" => $objeto,
                            "config" => CrudGenerator::turnNodbIntoReadonly($config),
                        ])->render();
                        break;
                    case "store":
                    case "update":
                    case "destroy":
                        if ($registro > 0) {
                            if (!is_object($objeto)) {
                                $objeto = $config["modelo"]::find($registro);
                            }
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
                            ]) . $getReturn)->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensajeStr);
                        } else {
                            $result = back()->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensajeStr);
                        }
                        break;
                }
            }
            if ($request->ajax() || $tipoReturn == 'json') {
                if (strtolower($request->_return) == 'datatablesjson') {
                    $result = [
                        "draw"            => intval($request->input('draw')),
                        "recordsTotal"    => intval($result[2]),
                        "recordsFiltered" => intval($result[3]),
                        "data"            => $result[1]
                    ];
                    return CrudGenerator::convert_from_latin1_to_utf8_recursively($result);
                } else {
                    $result = [
                        'status' => 200,
                        'statusText' => trans("crudgenerator::admin.messages.200"),
                        'title' => $titulo,
                        'result' => $result,
                    ];
                }
                if ($request->has('callback')) {
                    return response()->json($result, 200)->setCallback($request->callback);
                } else {
                    //return "<p></p><pre>" . print_r($result, true) . "</pre>";
                    return response()->json($result, 200);
                }
            } else {
                return $result;
            }
        } else {
            $result = [];
            switch ($action) {
                case "index":
                case "create":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelos::" . $action, ['modelo' => $modelo])
                    ];
                    break;
                case "show":
                case "edit":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelo::" . $action, ['modelo' => $modelo, 'registro' => $registro])
                    ];
                    break;
                case "store":
                case "destroy":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelos::index", ['modelo' => $modelo])
                    ];
                    break;
                case "update":
                    $result = [
                        config("sirgrimorum.crudgenerator.error_messages_key") => $mensajes['permission'],
                        config("sirgrimorum.crudgenerator.login_redirect_key") => route("sirgrimorum_modelo::edit", ['modelo' => $modelo, 'registro' => $registro])
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
