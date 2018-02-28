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
    } else {
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
if (isset($datos['placeholder'])) {
    $placeholder = $datos['placeholder'];
} else {
    $placeholder = "";
}
?>
<div class="form-group row">
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($columna, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        <div class="input-group w-50">
            @if (isset($datos["pre"]))
            <div class="input-group-prepend"><div class="input-group-text">{{ $datos["pre"] }}</div></div>
            @endif
            {{ Form::number($columna, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna, 'step' => 'any', 'placeholder'=>$placeholder,$readonly)) }}
            @if (isset($datos["post"]))
            <div class="input-group-append"><div class="input-group-text">{{ $datos["post"] }}</div></div>
            @endif
        </div>
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>