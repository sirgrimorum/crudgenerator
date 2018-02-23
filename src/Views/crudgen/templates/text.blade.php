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
if (isset($datos["readonly"])){
    $readonly = $datos["readonly"];
}else{
    $readonly = "";
}
if (isset($datos["placeholder"])){
    $placeholder = $datos['placeholder'];
}else{
    $placeholder="";
}
?>
<div class="form-group row">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        @if (isset($datos["pre"]) || isset($datos["post"]))
        <div class="input-group">
            @endif
            @if (isset($datos["pre"]))
                <div class="input-group-addon">{{ $datos["pre"] }}</div>
            @endif
            {{ Form::text($columna, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna, 'placeholder'=>$placeholder,$readonly)) }}
            @if (isset($datos["post"]))
                <div class="input-group-addon">{{ $datos["post"] }}</div>
            @endif
            @if (isset($datos["pre"]) || isset($datos["post"]))
        </div>
        @endif
        <small class="form-text text-muted" id="{{ $tabla . '_' . $columna }}_help">
            @if (isset($datos['description']))
            {{ $datos['description'] }}
            @endif
        </small>
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>
