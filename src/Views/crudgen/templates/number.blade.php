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
        $claseError = 'has-error';
    } 
}
if (isset($datos["readonly"])){
    $readonly = $datos["readonly"];
}else{
    $readonly = "";
}
?>
<div class="form-group {{ $claseError }}">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        @if (isset($datos["pre"]) || isset($datos["post"]))
        <div class="input-group">
            @endif
            @if (isset($datos["pre"]))
                <div class="input-group-addon">{{ $datos["pre"] }}</div>
            @endif
            {{ Form::number($columna, $dato, array('class' => 'form-control ' . $config['class_input'], 'id' => $tabla . '_' . $columna, 'step' => 'any', 'placeholder'=>$datos['placeholder'],$readonly)) }}
            @if (isset($datos["post"]))
                <div class="input-group-addon">{{ $datos["post"] }}</div>
            @endif
            @if (isset($datos["pre"]) || isset($datos["post"]))
        </div>
        @endif
        <span class="help-block" id="{{ $tabla . '_' . $columna }}_help">
            @if (isset($datos['description']))
            {{ $datos['description'] }}
            @endif
        </span>
        @if ($error_campo)
        <div class="alert alert-danger">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>