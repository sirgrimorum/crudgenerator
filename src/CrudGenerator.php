<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Sirgrimorum\CrudGenerator\Traits;
use Illuminate\Support\Facades\Lang;

class CrudGenerator
{

    use Traits\CrudStrings,
        Traits\CrudConfig,
        Traits\CrudFiles,
        Traits\CrudModels;

    protected $app;

    /**
     *
     * @param string $app Ipara nada
     */
    function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Generate create view for a model
     * @param array $config Configuration array
     * @param boolean $simple Optional True for a simple view (just the form)
     * @param boolean $botonModal Optional True for include only a button with a modal window
     * @return string Create form in HTML
     */
    public static function create($config, $simple = false, $botonModal = false)
    {
        //$config = CrudGenerator::translateConfig($config);
        if (request()->has('_itemRelSel')) {
            list($itemsRelSelCampo, $itemsRelSelId) = explode("|", request()->_itemRelSel);
            foreach ($config['campos'] as $clave => $relacion) {
                if ($clave != $itemsRelSelCampo) {
                    unset($config['campos'][$clave]);
                }
            }
        }
        if (!CrudGenerator::checkPermission($config, 0, 'create')) {
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        $modelo = strtolower(class_basename($config["modelo"]));
        $config = CrudGenerator::loadTodosFromConfig($config);
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::store", ["modelo" => $modelo]);
            if (Lang::has('crudgenerator::' . $modelo . '.labels.create')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.create");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.labels.create");
            }
        } elseif (is_array($config['url'])) {
            $config['url'] = Arr::get($config['url'], 'store', route("sirgrimorum_modelo::store", ["modelo" => $modelo]));
        }
        if (!isset($config['botones'])) {
            if (Lang::has('crudgenerator::' . $modelo . '.labels.create')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.create");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.labels.create");
            }
        }
        if (request()->has('_itemRelSel')) {
            $tabla = (new $config['campos'][$itemsRelSelCampo]['modelo'])->getTable();
            $config['formId'] = Arr::get($config, 'formId', $tabla . "_" . Str::random(5));
            $view = View::make('sirgrimorum::crudgen.templates.relationshipssel_simple', [
                'config' => CrudGenerator::loadDefaultClasses($config, true),
                'datoId' => $itemsRelSelId,
                'columna' => $itemsRelSelCampo,
                'tabla' => $tabla,
                'datos' => $config['campos'][$itemsRelSelCampo],
                'js_section' => $js_section,
                'css_section' => $css_section,
                'modelo' => $modelo
            ]);
        } else {
            $config['formId'] = Arr::get($config, 'formId', $config['tabla'] . "_" . Str::random(5));
            if ($botonModal) {
                $vista = 'sirgrimorum::admin.create.boton_modal';
            } else {
                $vista = 'sirgrimorum::crudgen.create';
            }
            $view = View::make($vista, [
                'config' => CrudGenerator::loadDefaultClasses($config, $simple),
                'tieneHtml' => CrudGenerator::hasTipo($config, ['html', 'article']),
                'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                'tieneSearch' => CrudGenerator::hasTipo($config, ['relationshipssel']),
                'tieneColor' => CrudGenerator::hasTipo($config, ['color']),
                'tieneCheckeador' => CrudGenerator::hasTipo($config, ['select', 'checkbox', 'radio']),
                'tieneFile' => CrudGenerator::hasTipo($config, ['file', 'files']),
                'tieneJson' => CrudGenerator::hasTipo($config, ['json']),
                'tieneInputFilter' => CrudGenerator::hasClave($config, 'inputfilter'),
                'js_section' => $js_section,
                'css_section' => $css_section,
                'modelo' => $modelo
            ]);
        }
        return $view->render();
    }

    /**
     * Generate view to show a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param boolean $simple Optional True for a simple view (just the form)
     * @param Model $registro Optional The Object
     * @param boolean $botonModal Optional True for include only a button with a modal window
     * @return string the Object in html
     */
    public static function show($config, $id = null, $simple = false, $registro = null, $botonModal = false)
    {
        //$config = CrudGenerator::translateConfig($config);
        $modelo = strtolower(class_basename($config["modelo"]));
        $config['id'] = Arr::get($config, "id", "id");
        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } elseif (is_object($id) && isset($id->{$config['id']})) {
                $registro = $id;
                $id = $registro->{$config['id']};
            } else {
                $registro = $modeloM::find($id);
            }
        }elseif ($id == null) {
            if (is_array($registro) && Arr::has($registro, $config['id'])) {
                $id = $registro[$config['id']];
            } elseif (is_object($registro) && isset($registro->{$config['id']})) {
                $id = $registro->{$config['id']};
            }
        }
        if ($id == null || $id == 0 || $registro == null || (is_array($registro) && count($registro) == 0)){
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => str_replace(":modelId", $id, trans('crudgenerator::admin.messages.not_found'))]);
        }
        if (!CrudGenerator::checkPermission($config, $id, 'show')) {
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        if ($botonModal) {
            $vista = 'sirgrimorum::admin.show.boton_modal';
        } else {
            $vista = 'sirgrimorum::crudgen.show';
        }
        $view = View::make($vista, array(
            'config' => CrudGenerator::loadDefaultClasses($config, $simple),
            'registro' => $registro,
            'js_section' => $js_section,
            'css_section' => $css_section,
            'modelo' => $modelo
        ));
        return $view->render();
    }

    /**
     * Generate de edit view of a model
     * @param array $config Configuration array
     * @param integer $id Key of the object
     * @param boolean $simple Optional True for a simple view (just the form)
     * @param Model $registro Optional The object
     * @param boolean $botonModal Optional True for include only a button with a modal window
     * @return HTML Edit form
     */
    public static function edit($config, $id = null, $simple = false, $registro = null, $botonModal = false)
    {
        //$config = CrudGenerator::translateConfig($config);
        $modelo = strtolower(class_basename($config["modelo"]));
        $config = CrudGenerator::loadTodosFromConfig($config);
        $config['formId'] = Arr::get($config, 'formId', $config['tabla'] . "_" . Str::random(5));
        $config['id'] = Arr::get($config, "id", "id");
        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } elseif (is_object($id) && isset($id->{$config['id']})) {
                $registro = $id;
                $id = $registro->{$config['id']};
            } else {
                $registro = $modeloM::find($id);
            }
        }elseif ($id == null) {
            if (is_array($registro) && Arr::has($registro, $config['id'])) {
                $id = $registro[$config['id']];
            } elseif (is_object($registro) && isset($registro->{$config['id']})) {
                $id = $registro->{$config['id']};
            }
        }
        if ($id == null || $id == 0 || $registro == null || (is_array($registro) && count($registro) == 0)){
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => str_replace(":modelId", $id, trans('crudgenerator::admin.messages.not_found'))]);
        }
        if (!CrudGenerator::checkPermission($config, $registro->getKey(), 'edit')) {
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::update", ["modelo" => $modelo, "registro" => $registro->id]);
            if (Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.labels.edit");
            }
        } elseif (is_array($config['url'])) {
            $config['url'] = Arr::get($config['url'], 'update', route("sirgrimorum_modelo::update", ["modelo" => $modelo, "registro" => $registro->id]));
        }
        if (Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
            $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
        } else {
            $config['botones'] = trans("crudgenerator::admin.layout.labels.edit");
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        if ($botonModal) {
            $vista = 'sirgrimorum::admin.edit.boton_modal';
        } else {
            $vista = 'sirgrimorum::crudgen.edit';
        }
        $view = View::make($vista, [
            'config' => CrudGenerator::loadDefaultClasses($config, $simple),
            'registro' => $registro,
            'tieneHtml' => CrudGenerator::hasTipo($config, ['html', 'article']),
            'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
            'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
            'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
            'tieneSearch' => CrudGenerator::hasTipo($config, ['relationshipssel']),
            'tieneColor' => CrudGenerator::hasTipo($config, ['color']),
            'tieneCheckeador' => CrudGenerator::hasTipo($config, ['select', 'checkbox', 'radio']),
            'tieneFile' => CrudGenerator::hasTipo($config, ['file', 'files']),
            'tieneJson' => CrudGenerator::hasTipo($config, ['json']),
            'tieneInputFilter' => CrudGenerator::hasClave($config, 'inputfilter'),
            'js_section' => $js_section,
            'css_section' => $css_section,
            'modelo' => $modelo
        ]);
        return $view->render();
    }

    /**
     * Generate a list of objects of a model
     * @param array $config Configuration array
     * @param boolean $modales Optional True if you want to use modals for the crud actions
     * @param boolean $simple Optional True for a simple view (just the table)
     * @param Collection|Builder $registros Optional Collection of objects or query to show
     * @return HTML Table with the objects
     */
    public static function lists($config, $modales = false, $simple = false, $registros = null)
    {
        //$config = CrudGenerator::translateConfig($config);
        if (!CrudGenerator::checkPermission($config, 0, 'index')) {
            return View::make('sirgrimorum::crudgen.partials.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        $modeloM = $config['modelo'];
        $usarAjax = Arr::get($config, 'ajax', false);
        $serverSide = Arr::get($config, 'serverSide', false) && $usarAjax;
        if ($usarAjax == false) {
            if ($registros == null) {
                $registros = CrudGenerator::lists_array($config, $registros, 'complete');
            }
            //$registros = CrudGenerator::filterWithQuery($registros, $config);
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        $modelo = basename($modeloM);
        if (isset($config['botones']) && is_array($config['botones'])) {
            foreach (Arr::only($config['botones'], ['create', 'show', 'edit', 'remove']) as $butName => $button) {
                if (!is_string($button)) {
                    unset($config['botones'][$butName]);
                }
            }
            $config['botones'] = array_merge(CrudGenerator::generateArrBotones($modelo, $config), $config['botones']);
        } else {
            $config['botones'] = CrudGenerator::generateArrBotones($modelo, $config);
        }
        $tienePrefiltro = CrudGenerator::hasValor($config, 'datatables', 'prefiltro');
        $preFiltros = false;
        if ($tienePrefiltro) {
            $preFiltroStr = Arr::get($_COOKIE, strtolower($modelo) . "_index_preFiltros", false);
            if (request()->has("preFiltros") && CrudGenerator::isJsonString(request()->get("preFiltros", ""))) {
                $preFiltros = json_decode(request()->get("preFiltros"), true);
            } elseif ($preFiltroStr != false && CrudGenerator::isJsonString($preFiltroStr)) {
                $preFiltros = json_decode($preFiltroStr, true);
            }
        }
        $view = View::make('sirgrimorum::crudgen.list', [
            'config' => CrudGenerator::loadDefaultClasses($config, $simple),
            'registros' => $registros,
            'usarAjax' => $usarAjax,
            'serverSide' => $serverSide,
            'tienePrefiltro' => $tienePrefiltro,
            'preFiltros' => $preFiltros,
            'modales' => $modales,
            'js_section' => $js_section,
            'css_section' => $css_section,
            'modelo' => strtolower($modelo)
        ]);
        return $view->render();
    }
}
