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
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
if (isset($datos['multiple'])) {
    if ($datos['multiple'] == 'multiple') {
        $nomColumna = $columna . "[]";
        $arrayAttr = array('multiple' => 'multiple', 'class' => 'form-control ' . $config['class_input'], 'id' => $tabla . '_' . $columna, $readonly);
    } else {
        $nomColumna = $columna;
        $arrayAttr = array('class' => 'form-control ' . $config['class_input'], 'id' => $tabla . '_' . $columna, $readonly);
    }
} else {
    $nomColumna = $columna;
    $arrayAttr = array('class' => 'form-control ' . $config['class_input'], 'id' => $tabla . '_' . $columna, $readonly);
}
?>
<div class="form-group {{ $claseError }}">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        {{ Form::select($nomColumna, $datos['opciones'], $dato, $arrayAttr) }}
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