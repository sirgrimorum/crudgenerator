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
        <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($plurales) }}</li>
    </ol>
</nav>
<h1>{{ ucfirst($plurales) }}</h1>

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
if (($textConfirm = trans('crudgenerator::' . strtolower($modelo) . '.messages.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy') {
    $textConfirm = trans('crudgenerator::admin.messages.confirm_destroy');
}
$config['botones'] = [
    'show' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId") . "' title='" . trans('crudgenerator::datatables.buttons.t_show') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.show") . "</a>",
    'edit' => "<a class='btn btn-success' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/edit") . "' title='" . trans('crudgenerator::datatables.buttons.t_edit') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.edit") . "</a>",
    'remove' => "<a class='btn btn-danger' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy") . "' data-confirm='" . $textConfirm . "' data-yes='" . trans('crudgenerator::admin.layout.labels.yes') . "' data-no='" . trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator.icons.confirm') . "' data-confirmtitle='' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::datatables.buttons.t_remove') . " " . $plurales . "'>". trans("crudgenerator::datatables.buttons.remove") . "</a>",
    'create' => "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "s/create") . "' title='" . trans('crudgenerator::datatables.buttons.t_create') . " " . $singulares . "'>". trans("crudgenerator::datatables.buttons.create") . "</a>",
];
/*$config['botones'] = [
    'show' => url($base_url . "/" . strtolower($modelo) . "/:modelId"),
    'edit' => url($base_url . "/" . strtolower($modelo) . "/:modelId/edit"),
    'remove' => url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy"),
    'create' => url($base_url . "/" . strtolower($modelo) . "s/create"),
];*/
?>
<div class='container'>
    {!! CrudGenerator::lists($config, config('sirgrimorum.crudgenerator.use_modals')) !!}
</div>
@stop
