<?php
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = $modelo;
}
?>

<div class="modal fade" id="{{$modelo}}_create_modal" tabindex="-1" role="dialog" aria-labelledby="{{$modelo}}_create_modalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{$modelo}}_create_modalLabel">{{ \Illuminate\Support\Arr::get(__("crudgenerator::" . strtolower($modelo) . ".titulos"), "create", __('crudgenerator::admin.layout.labels.create') . " " .ucfirst($singulares)) }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <?php
                //$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
                //$config['botones'] = trans("crudgenerator::article.labels.create");
                //$config['url'] = url($base_url . "/" . strtolower($modelo) . "/store");
                ?>
                {!! CrudGenerator::create($config,true) !!}
            </div>
        </div>
    </div>
</div>

