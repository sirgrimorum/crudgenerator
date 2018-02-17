@extends("sirgrimorum::admin/templates/html")
<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>
@section('contenido')
<div class="modal fade" id="{{$modelo}}_index_modal" tabindex="-1" role="dialog" aria-labelledby="{{$modelo}}_index_modalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title" id="{{$modelo}}_index_modalLabel">{{ ucfirst(trans('crudgenerator::' . strtolower($modelo) . '.labels.plural')) }}</h3>
            </div>
            <div class="modal-body">
                @if (Session::has('message'))
                <div class="alert alert-info">{{ Session::pull('message') }}</div>
                @endif
                <?php
                //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
                if (($textConfirm = trans('crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy')) == 'crudgenerator::' . strtolower($modelo) . '.mensajes.confirm_destroy') {
                    $textConfirm = trans('crudgenerator::admin.mensajes.confirm_destroy');
                }
                $config['botones'] = [
                    "<a class='btn btn-info' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId") . "' title='" . trans('crudgenerator::admin.layout.ver') . "'><span class='glyphicon glyphicon-info-sign' aria-hidden='true'></span></a>",
                    "<a class='btn btn-success' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/edit") . "' title='" . trans('crudgenerator::admin.layout.editar') . "'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></a>",
                    "<a class='btn btn-danger' href='" . url($base_url . "/" . strtolower($modelo) . "/:modelId/destroy") . "' data-confirm='" . $textConfirm . "' data-yes='" . $textConfirm = trans('crudgenerator::admin.layout.labels.yes') . "' data-yes='" . $textConfirm = trans('crudgenerator::admin.layout.labels.no') . "' data-confirmtheme='" . config('sirgrimorum.crudgenerator..confirm_theme') . "' data-confirmicon='" . config('sirgrimorum.crudgenerator..confirm_icon') . "' data-method='delete' rel='nofollow' title='" . trans('crudgenerator::admin.layout.borrar') . "'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></a>",
                ];
                ?>
                    {!! CrudLoader::lists($config) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('crudgenerator::admin.layout.labels.close')}}</button>
                <a href='{{ url($base_url . "/" . $plural .'/create') }}' class='pull-right btn btn-info' >{{ trans('crudgenerator::admin.layout.crear') }} {{ $singulares }}</a>
            </div>
        </div>
    </div>
</div>
<button type="button" data-toggle="modal" data-target="#{{$modelo}}_index_modal">Launch modal</button>
@stop