<?php
$dato = old($columna);
if ($dato == "") {
    try {
        $dato = $registro->{$columna};
    } catch (Exception $ex) {
        $dato = "";
    }
}
if ($dato == "") {
    if (isset($datos["valor"])) {
        $dato = $datos["valor"];
    }
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($columna)) {
        $error_campo = true;
        $claseError = 'is-invalid';
    }else{
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
if (isset($datos["placeholder"])) {
    $placeholder = $datos['placeholder'];
} else {
    $placeholder = "";
}
?>
<div class="form-group row">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text rounded-left">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($columna . "_name", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($columna, ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-outline-success" title="{{trans("crudgenerator::admin.layout.labels.add")}}"><i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
        </div>
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
        <small class="form-text text-muted" id="{{ $tabla . '_' . $columna }}_help">
            @if (isset($datos['description']))
            {{ $datos['description'] }}
            @endif
        </small>
    </div>
</div>