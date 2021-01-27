@extends("sirgrimorum::admin/templates/html", ["modeloActual"=>$modelo])
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
@push('menuobj')
@endpush

@section('contenido')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url($base_url . "/" . $plural) }}">{{ ucfirst($plurales) }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "create", __('crudgenerator::admin.layout.labels.create') . " " .ucfirst($singulares))  }}</li>
    </ol>
</nav>
<h1>{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "create", __('crudgenerator::admin.layout.labels.create') . " " .ucfirst($singulares)) }}</h1>

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.create");
//$config['url'] = url($base_url . "/" . strtolower($modelo) . "/store");
?>
<div class='container'>
    {!! CrudGenerator::create($config) !!}
</div>
@stop
