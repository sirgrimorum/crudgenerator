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
use Illuminate\Support\Facades\File;
use Sirgrimorum\CrudGenerator\SuperClosure;
use Sirgrimorum\CrudGenerator\Traits;

class CrudGenerator {

    use Traits\CrudStrings,
        Traits\CrudConfig,
        Traits\CrudFiles,
        Traits\CrudModels;

    protected $app;

    /**
     * 
     * @param string $app Ipara nada
     */
    function __construct($app) {
        $this->app = $app;
    }

    /**
     * Generate create view for a model
     * @param array $config Configuration array
     * @param boolean $simple Optional True for a simple view (just the form)
     * @return string Create form in HTML
     */
    public static function create($config, $simple = false) {
        //$config = CrudGenerator::translateConfig($config);
        if (request()->has('_itemRelSel')) {
            list($itemsRelSelCampo, $itemsRelSelId) = explode("|", request()->_itemRelSel);
            foreach ($config['campos'] as $clave => $relacion) {
                if ($clave != $itemsRelSelCampo) {
                    unset($config['campos'][$clave]);
                }
            }
        }
        if (!CrudGenerator::checkPermission($config)) {
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
            if (\Lang::has('crudgenerator::' . $modelo . '.labels.create')) {
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
            $view = View::make('sirgrimorum::crudgen.create', [
                        'config' => $config,
                        'tieneHtml' => CrudGenerator::hasTipo($config, 'html'),
                        'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                        'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                        'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                        'tieneSearch' => CrudGenerator::hasTipo($config, [ 'relationshipssel']),
                        'tieneFile' => CrudGenerator::hasTipo($config, [ 'file', 'files']),
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
     * @return string the Object in html
     */
    public static function show($config, $id = null, $simple = false, $registro = null) {
        //$config = CrudGenerator::translateConfig($config);
        $modelo = strtolower(class_basename($config["modelo"]));
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
        if (!CrudGenerator::checkPermission($config, $registro->getKey())) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        $view = View::make('sirgrimorum::crudgen.show', array(
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
     * @return HTML Edit form
     */
    public static function edit($config, $id = null, $simple = false, $registro = null) {
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
        if (!CrudGenerator::checkPermission($config, $registro->getKey())) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        if ($config['url'] == "Sirgrimorum_CrudAdministrator") {
            $config['url'] = route("sirgrimorum_modelo::update", ["modelo" => $modelo, "registro" => $registro->id]);
            if (\Lang::has('crudgenerator::' . $modelo . '.labels.edit')) {
                $config['botones'] = trans("crudgenerator::$modelo.labels.edit");
            } else {
                $config['botones'] = trans("crudgenerator::admin.layout.editar");
            }
        }
        if (!$simple) {
            $js_section = config("sirgrimorum.crudgenerator.js_section");
            $css_section = config("sirgrimorum.crudgenerator.css_section");
        } else {
            $js_section = "";
            $css_section = "";
        }
        $view = View::make('sirgrimorum::crudgen.edit', [
                    'config' => $config,
                    'registro' => $registro,
                    'tieneHtml' => CrudGenerator::hasTipo($config, 'html'),
                    'tieneDate' => CrudGenerator::hasTipo($config, ['date', 'datetime', 'time']),
                    'tieneSlider' => CrudGenerator::hasTipo($config, 'slider'),
                    'tieneSelect' => CrudGenerator::hasTipo($config, ['select', 'relationship', 'relationships']),
                    'tieneSearch' => CrudGenerator::hasTipo($config, [ 'relationshipssel']),
                    'tieneFile' => CrudGenerator::hasTipo($config, [ 'file', 'files']),
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
     * @param Model() $registros Optional Array of objects to show
     * @return HTML Table with the objects
     */
    public static function lists($config, $modales = false, $simple = false, $registros = null) {
        //$config = CrudGenerator::translateConfig($config);
        if (!CrudGenerator::checkPermission($config)) {
            return View::make('sirgrimorum::crudgen.error', ['message' => trans('crudgenerator::admin.messages.permission')]);
        }
        $modeloM = $config['modelo'];
        if ($registros == null) {
            $registros = $modeloM::all();
            //$registros = $modeloM::all();
        }
        $registros = \Sirgrimorum\CrudGenerator\CrudGenerator::filterWithQuery($registros, $config);
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
                $plurales = $plural;
            }
            if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
                $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
            } else {
                $singulares = $modelo;
            }
            $config['botones'] = [
                'show' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId") . "' title='" . trans('crudgenerator::datatables.buttons.t_show') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.show") . "</a>",
                'edit' => "<a class='btn btn-success' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/edit") . "' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.edit") . "</a>",
                'remove' => "<a class='btn btn-danger' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy") . "' data-confirm='" . $textConfirm . "' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " " . $plurales . "'>" . trans("crudgenerator::datatables.buttons.remove") . "</a>",
                'create' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "s/create") . "' title='" . trans('crudgenerator::datatables.buttons.t_create') . " " . $singulares . "'>" . trans("crudgenerator::datatables.buttons.create") . "</a>",
            ];
        }
        $view = View::make('sirgrimorum::crudgen.list', [
                    'config' => $config,
                    'registros' => $registros,
                    'modales' => $modales,
                    'js_section' => $js_section,
                    'css_section' => $css_section,
                    'modelo' => strtolower($modelo)
        ]);
        return $view->render();
    }

}
