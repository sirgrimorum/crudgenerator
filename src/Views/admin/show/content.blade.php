@extends("sirgrimorum::admin/templates/html")
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
@section('menuobj')
<li><a href="{{ URL::to($base_url . "/" . $plural .'/create') }}">{{ trans('crudgenerator::admin.layout.crear') }} {{ $singulares }}</a></li>
@stop

@section('contenido')
<ol class="breadcrumb">
    <li><a href="{{ url($base_url . "/" . $plural) }}">{{ ucfirst($plurales) }}</a></li>
    <li class="active">{{ trans('crudgenerator::admin.layout.ver') }} {{ ucfirst($singulares) }}</li>
</ol>
<!--h1>{{ trans('crudgenerator::admin.layout.ver') }} {{ ucfirst($singulares) }}</h1-->

@if (Session::has('message'))
<div class="alert alert-info">{{ Session::pull('message') }}</div>
@endif
<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
$config['botones'] = trans("crudgenerator::article.labels.create");
?>
<div class='container'>
    {!! CrudLoader::show($config,$registro) !!}
</div>
@stop
