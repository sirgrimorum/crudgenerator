<?php

namespace Sirgrimorum\CrudGenerator;

use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
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
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
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
                $config['botones'] = trans("crudgenerator::admin.layout.crear");
            }
        } elseif (is_array($config['url'])) {
            $config['url'] = Arr::get($config['url'], 'store', route("sirgrimorum_modelo::store", ["modelo" => $modelo]));
        }
        if (!isset($config['botones'])) {
            if (Lang::has('crudgenerator::' . $modelo . '.labels.create')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.create");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.crear");
            }
        }
        if (request()->has('_itemRelSel')) {
            $view = View::make('sirgrimorum::crudgen.templates.relationshipssel_simple', [
                'config' => $config,
                'datoId' => $itemsRelSelId,
                'columna' => $itemsRelSelCampo,
                'tabla' => (new $config['campos'][$itemsRelSelCampo]['modelo'])->getTable(),
                'datos' => $config['campos'][$itemsRelSelCampo],
                'js_section' => $js_section,
                'css_section' => $css_section,
                'modelo' => $modelo
            ]);
        } else {
            if ($botonModal) {
                $vista = 'sirgrimorum::admin.create.boton_modal';
            } else {
                $vista = 'sirgrimorum::crudgen.create';
            }
            $view = View::make($vista, [
                'config' => $config,
                'tieneHtml' => CrudGenerator::hasTipo($config, ['html', 'article']),
                'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                'tieneSearch' => CrudGenerator::hasTipo($config, ['relationshipssel']),
                'tieneColor' => CrudGenerator::hasTipo($config, ['color']),
                'tieneCheckeador' => CrudGenerator::hasTipo($config, ['select', 'checkbox', 'radio']),
                'tieneFile' => CrudGenerator::hasTipo($config, ['file', 'files']),
                'tieneJson' => CrudGenerator::hasTipo($config, ['json']),
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
        if ($registro == null) {
            if ($id != null && is_object($id)) {
                $registro = $id;
                $id = $registro->{$config['id']};
            } else {
                $registro = CrudGenerator::registry_array($config, $id, 'complete');
                $id = $registro[$config['id']];
            }
        } elseif ($id == null) {
            if (is_array($registro)) {
                $id = $registro[$config['id']];
            } else {
                $id = $registro->{$config['id']};
            }
        }
        if (!CrudGenerator::checkPermission($config, $id, 'show')) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
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
            'config' => $config,
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

        if ($registro == null) {
            $modeloM = ucfirst($config['modelo']);
            if ($id == null) {
                $registro = $modeloM::first();
            } elseif (is_object($id)) {
                $registro = $id;
                $id = $registro->getKey();
            } else {
                $registro = $modeloM::find($id);
            }
        }
        if (!CrudGenerator::checkPermission($config, $registro->getKey(), 'edit')) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::update", ["modelo" => $modelo, "registro" => $registro->id]);
            if (Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.editar");
            }
        }elseif (is_array($config['url'])) {
            $config['url'] = Arr::get($config['url'], 'update', route("sirgrimorum_modelo::update", ["modelo" => $modelo, "registro" => $registro->id]));
        }
        if (Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
            $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
        } else {
            $config['botones'] = trans("crudgenerator::admin.layout.editar");
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
            'config' => $config,
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
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
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
        if (!isset($config['botones'])) {
            $base_url = route("sirgrimorum_home", App::getLocale());
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
                    "show" => url($base_url . "/" . strtolower($modelo) . "/:modelId"),
                    "edit" => url($base_url . "/" . strtolower($modelo) . "/:modelId/edit"),
                    "remove" => url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy"),
                    "create" => url($base_url . "/" . strtolower($modelo) . "s/create"),
                ];
            }elseif (is_array($config['url'])) {
                $urls = [
                    "show" =>  Arr::get($config['url'], 'show', url($base_url . "/" . strtolower($modelo) . "/:modelId")),
                    "edit" => Arr::get($config['url'], 'edit', url($base_url . "/" . strtolower($modelo) . "/:modelId/edit")),
                    "remove" => Arr::get($config['url'], 'remove', url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy")),
                    "create" => Arr::get($config['url'], 'create', url($base_url . "/" . strtolower($modelo) . "s/create")),
                ];
            }
            $config['botones'] = [
                'show' => "<a class='btn btn-info' href='{$urls['show']}' title='" . trans('crudgenerator::datatables.buttons.t_show') . " $singulares'>" . trans("crudgenerator::datatables.buttons.show") . "</a>",
                'edit' => "<a class='btn btn-success' href='{$urls['edit']}' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " $singulares'>" . trans("crudgenerator::datatables.buttons.edit") . "</a>",
                'remove' => "<a class='btn btn-danger' href='{$urls['remove']}' data-confirm='$textConfirm' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " $plurales'>" . trans("crudgenerator::datatables.buttons.remove") . "</a>",
                'create' => "<a class='btn btn-info' href='{$urls['create']}' title='" . trans('crudgenerator::datatables.buttons.t_create') . " $singulares'>" . trans("crudgenerator::datatables.buttons.create") . "</a>",
            ];
        }
        $view = View::make('sirgrimorum::crudgen.list', [
            'config' => $config,
            'registros' => $registros,
            'usarAjax' => $usarAjax,
            'serverSide' => $serverSide,
            'tienePrefiltro' => CrudGenerator::hasValor($config, 'datatables', 'prefiltro'),
            'modales' => $modales,
            'js_section' => $js_section,
            'css_section' => $css_section,
            'modelo' => strtolower($modelo)
        ]);
        return $view->render();
    }
}
