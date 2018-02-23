<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>
<div class="modal fade" id="{{$modelo}}_show_modal" tabindex="-1" role="dialog" aria-labelledby="{{$modelo}}_show_modalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{$modelo}}_show_modalLabel">{{ trans('crudgenerator::admin.layout.ver') }} {{ ucfirst($singulares) }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                
                <?php
                //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
                $config['botones'] = trans("crudgenerator::article.labels.create");
                ?>
                {!! CrudLoader::show($config,$registro,true) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('crudgenerator::admin.layout.labels.close')}}</button>
            </div>
        </div>
    </div>
</div>
<!--button type="button" data-toggle="modal" data-target="#{{$modelo}}_show_modal">Launch modal</button-->
