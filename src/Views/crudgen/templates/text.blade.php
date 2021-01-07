<?php
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId);
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
    } else {
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
$extraClassDiv = array_get($datos, 'extraClassDiv', "");
$extraClassInput = array_get($datos, 'extraClassInput', "");
$extraDataInput = array_get($datos, 'extraDataInput', []);
$help = array_get($datos, 'help', "");
?>
<div class="form-group row {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        @if (isset($datos["pre"]) || isset($datos["post"]))
        <div class="input-group {{ $claseError }}">
            @endif
            @if (isset($datos["pre"]))
            <div class="input-group-prepend"><div class="input-group-text">{{ $datos["pre"] }}</div></div>
            @endif
            {{ Form::text($extraId, $dato, array_merge(
                $extraDataInput,
                ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, 'placeholder'=>$placeholder,$readonly])) }}
            @if (isset($datos["post"]))
            <div class="input-group-append"><div class="input-group-text">{{ $datos["post"] }}</div></div>
            @endif
            @if (isset($datos["pre"]) || isset($datos["post"]))
        </div>
        @endif
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
        @if($help != "")
        <small class="form-text text-muted mt-0">
            {{ $help }}
        </small>
        @endif
    </div>
</div>
