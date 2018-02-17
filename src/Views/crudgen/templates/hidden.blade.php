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
?>
{{ Form::hidden($columna, $dato, array('class' => 'form-control', 'id' => $tabla . '_' . $columna)) }}
