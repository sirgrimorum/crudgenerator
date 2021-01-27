<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>
<!--h1>{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "edit", __('crudgenerator::admin.layout.labels.edit') . " " .ucfirst($singulares)) }}</h1-->

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.edit");
//$config['url'] = url($base_url . "/" . strtolower($modelo) . "/" . $registro . "/update");
?>
{!! CrudGenerator::edit($config,$registro,true) !!}
