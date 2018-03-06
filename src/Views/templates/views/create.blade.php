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
        <li class="breadcrumb-item"><a href="[[ route('home') ]]">[[trans("crudgenerator::admin.layout.labels.home")]]</a></li>
        <li class="breadcrumb-item"><a href="[[ route('{modelo}.index') ]]">[[ ucfirst($plurales) ]]</a></li>
        <li class="breadcrumb-item active" aria-current="page">[[ trans('crudgenerator::admin.layout.crear') ]] [[ ucfirst($singulares) ]]</li>
    </ol>
</nav>
<h1>[[ trans('crudgenerator::admin.layout.crear') ]] [[ ucfirst($singulares) ]]</h1>
<div class='container'>
    [!! CrudGenerator::create($config) !!]
</div>
</div>
*stop
