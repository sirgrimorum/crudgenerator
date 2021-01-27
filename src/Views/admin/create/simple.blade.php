<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>
<!--h1>{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "create", __('crudgenerator::admin.layout.labels.create') . " " .ucfirst($singulares)) }}</h1-->

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.create");
//$config['url'] = url($base_url . "/" . strtolower($modelo) . "/store");
?>
{!! CrudGenerator::create($config,true) !!}
