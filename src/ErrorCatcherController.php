<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Sirgrimorum\CrudGenerator\Models\Catchederror;
use Sirgrimorum\CrudGenerator\CrudGenerator;

class ErrorCatcherController extends BaseController
{

    /**
     * The errorcatcher name.
     *
     * @var  string
     */
    protected $modelName = 'catchederror';

    /**
     * The plan config array.
     *
     * @var  array
     */
    protected $config;

    /**
     * Ruta para retornar luego de editar o crear
     * 
     * @var string
     */
    protected $routeRedirect;

    /**
     * Create a new controller instance.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->config = CrudGenerator::getConfig($this->modelName);
        $this->routeRedirect = route('sirgrimorum_modelos::index', ['modelo' => 'errorcatcher', 'localecode' => App::getLocale()]);
        //$this->routeRedirect = route('plan.index');
    }

    public function NoReport(Request $request, Catchederror $catchederror)
    {
        if (CrudGenerator::checkPermission($this->config, $catchederror, "default")) {
            $catchederror->reportar = false;
            $catchederror->save();
            $mensajes = [];
            if (is_array(trans("crudgenerator::catchederror.messages"))) {
                $mensajes = array_merge($mensajes, trans("crudgenerator::catchederror.messages"));
            }
            $mensaje = str_replace([":modelName", ":modelId"], [$catchederror->{$this->config['nombre']}, $catchederror->{$this->config['id']}], Arr::get($mensajes, "no_report_success",""));
            return redirect()->back()->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensaje);
        }
    }

    public function Report(Request $request, Catchederror $catchederror)
    {
        if (CrudGenerator::checkPermission($this->config, $catchederror, "default")) {
            $catchederror->reportar = true;
            $catchederror->save();
            $mensajes = [];
            if (is_array(trans("crudgenerator::catchederror.messages"))) {
                $mensajes = array_merge($mensajes, trans("crudgenerator::catchederror.messages"));
            }
            $mensaje = str_replace([":modelName", ":modelId"], [$catchederror->{$this->config['nombre']}, $catchederror->{$this->config['id']}], Arr::get($mensajes, "report_success",""));
            return redirect()->back()->with(config("sirgrimorum.crudgenerator.status_messages_key"), $mensaje);
        }
    }
}