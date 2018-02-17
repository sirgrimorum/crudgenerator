<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>
<h1>{{ trans('crudgenerator::admin.layout.crear') }} {{ ucfirst($singulares) }}</h1>

@if (Session::has('message'))
<div class="alert alert-info">{{ Session::pull('message') }}</div>
@endif
<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.create");
//$config['url'] = url($base_url . "/" . strtolower($modelo) . "/store");
?>
{!! CrudLoader::create($config) !!}
