*extends('layouts.app')

{?php}
if (Lang::has("crudgenerator::{model}.labels.plural")) {
    $plurales = trans("crudgenerator::{model}.labels.plural");
} else {
    $plurales = '{Model}s';
}
if (Lang::has("crudgenerator::{model}.labels.singular")) {
    $singulares = trans("crudgenerator::{model}.labels.singular");
} else {
    $singulares = 'Model';
}
{php?}

*section('content')
<div class="container">
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="[[ route('home') ]]">[[trans("crudgenerator::admin.layout.labels.home")]]</a></li>
        <li class="breadcrumb-item active" aria-current="page">[[ ucfirst($plurales) ]]</li>
    </ol>
</nav>
<h1>[[ ucfirst($plurales) ]]</h1>

{?php}
if (($textConfirm = trans('crudgenerator::{model}.messages.confirm_destroy')) == 'crudgenerator::{model}.mensajes.confirm_destroy') {
    $textConfirm = trans('crudgenerator::admin.messages.confirm_destroy');
}

$config['botones'] = [
    @if($localized)
    'show' => "<a class='btn btn-info' href='" . route("{model}.show",["localecode"=>App::getLocale(),"{model}"=>":modelId"]) . "' title='" . trans('crudgenerator::datatables.buttons.t_show') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.show") . "</a>",
    'edit' => "<a class='btn btn-success' href='" . route("{model}.edit",["localecode"=>App::getLocale(),"{model}"=>":modelId"]) . "' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.edit") . "</a>",
    'remove' => "<a class='btn btn-danger' href='" . route("{model}.destroy",["localecode"=>App::getLocale(),"{model}"=>":modelId"]) . "' data-confirm='" . $textConfirm . "' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " " . $plurales . "'>". trans("crudgenerator::datatables.buttons.remove") . "</a>",
    'create' => "<a class='btn btn-info' href='" .route("{model}.create",App::getLocale()) . "' title='" . trans('crudgenerator::datatables.buttons.t_create') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.create") . "</a>",
    @else
    'show' => "<a class='btn btn-info' href='" . route("{model}.show",":modelId") . "' title='" . trans('crudgenerator::datatables.buttons.t_show') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.show") . "</a>",
    'edit' => "<a class='btn btn-success' href='" . route("{model}.edit",":modelId") . "' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.edit") . "</a>",
    'remove' => "<a class='btn btn-danger' href='" . route("{model}.destroy",":modelId") . "' data-confirm='" . $textConfirm . "' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.confirm_icon') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " " . $plurales . "'>". trans("crudgenerator::datatables.buttons.remove") . "</a>",
    'create' => "<a class='btn btn-info' href='" .route("{model}.create") . "' title='" . trans('crudgenerator::datatables.buttons.t_create') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.create") . "</a>",
    @endif
];
/*$config['botones'] = [
    @if($localized)
    'show' => route("{model}.show",["localecode"=>App::getLocale(),"{model}"=>":modelId"]),
    'edit' => route("{model}.edit",["localecode"=>App::getLocale(),"{model}"=>":modelId"]),
    'remove' => route("{model}.destroy",["localecode"=>App::getLocale(),"{model}"=>":modelId"]),
    'create' => route("{model}.create",App::getLocale()),
    @else
    'show' => route("{model}.show",":modelId"),
    'edit' => route("{model}.edit",":modelId"),
    'remove' => route("{model}.destroy",":modelId"),
    'create' => route("{model}.create"),
    @endif
];*/
$modales = false;
/**
 * To use CrudGenerator Modals, uncomment
 */
/*
unset($config['botones']);
$modales = true;
/*
 */
{php?}
<div class='container'>
    [!! CrudGenerator::lists($config,$modales) !!]
</div>
</div>
*stop
