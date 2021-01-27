<?php
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
?>
<!--h1>{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "index", ucfirst($plurales)) }} <a href='{{ url($base_url . "/" . $plural .'/create') }}' class='pull-right btn btn-info' ><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> {{ trans('crudgenerator::admin.layout.labels.create') }} {{ $singulares }}</a></h1-->

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
if (($textConfirm = trans('crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy') {
    $textConfirm = trans('crudgenerator::admin.mensajes.confirm_destroy');
}
/*$config['botones'] = [
    "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId") . "' title='" . trans('crudgenerator::admin.layout.labels.show') . "'><span class='glyphicon glyphicon-info-sign' aria-hidden='true'></span></a>",
    "<a class='btn btn-success' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/edit") . "' title='" . trans('crudgenerator::admin.layout.labels.edit') . "'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></a>",
    "<a class='btn btn-danger' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy") . "' data-confirm='" . $textConfirm . "' data-yes='" . $textConfirm = trans('crudgenerator::admin.layout.labels.yes') . "' data-yes='" . $textConfirm = trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator..confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.icons.confirm') . "' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::admin.layout.labels.remove') . "'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></a>",
];*/
?>
{!! CrudGenerator::lists($config,false,true) !!}
