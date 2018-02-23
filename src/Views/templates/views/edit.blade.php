*extends('layouts.app')

{?php}
if (Lang::has("crudgenerator::{model}.labels.plural")) {
    $plurales = trans("crudgenerator::{model}.labels.plural");
} else {
    $plurales = $plural;
}
if (Lang::has("crudgenerator::{model}.labels.singular")) {
    $singulares = trans("crudgenerator::{model}.labels.singular");
} else {
    $singulares = $modelo;
}
{php?}

*section('content')
<div class="container">
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @if($localized)
        <li class="breadcrumb-item"><a href="[[ route('home',App::getLocale()) ]]">[[trans("crudgenerator::admin.layout.labels.home")]]</a></li>
        <li class="breadcrumb-item"><a href="[[ route('{modelo}.index',App::getLocale()) ]]">[[ ucfirst($plurales) ]]</a></li>
        @else
        <li class="breadcrumb-item"><a href="[[ route('home') ]]">[[trans("crudgenerator::admin.layout.labels.home")]]</a></li>
        <li class="breadcrumb-item"><a href="[[ route('{modelo}.index') ]]">[[ ucfirst($plurales) ]]</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">[[ trans('crudgenerator::admin.layout.editar') ]] [[ ucfirst($singulares) ]]</li>
    </ol>
</nav>
<h1>[[ trans('crudgenerator::admin.layout.editar') ]] [[ ucfirst($singulares) ]]</h1>

<div class='container'>
    [!! CrudLoader::edit($config,${model}) !!]
</div>
</div>
*stop
