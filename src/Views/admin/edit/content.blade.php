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
<li><a class="nav-link" href="{{ URL::to($base_url . "/" . $plural .'/create') }}">{{ trans('crudgenerator::admin.layout.crear') }} {{ $singulares }}</a></li>
@endpush

@section('contenido')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url($base_url . "/" . $plural) }}">{{ ucfirst($plurales) }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ trans('crudgenerator::admin.layout.editar') }} {{ ucfirst($singulares) }}</li>
    </ol>
</nav>
<h1>{{ trans('crudgenerator::admin.layout.editar') }} {{ ucfirst($singulares) }}</h1>

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.edit");
//$config['url'] = url($base_url . "/" . strtolower($modelo) . "/" . $registro . "/update");
?>
<div class='container'>
    {!! CrudGenerator::edit($config,$registro) !!}
</div>
@stop
