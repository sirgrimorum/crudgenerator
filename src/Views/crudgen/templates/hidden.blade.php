<?php
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId);
if ($dato == "") {
    try {
        if (\Sirgrimorum\CrudGenerator\CrudGenerator::hasRelation($registro, $columna)) {
            if (isset($datos['id'])) {
                $idKeyName = $datos['id'];
            } else {
                $idKeyName = $registro->{$columna}->getKeyName();
            }
            $dato = $registro->{$columna}->{$idKeyName};
        } else {
            $dato = $registro->{$columna};
        }
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
{{ Form::hidden($extraId, $dato, array('class' => 'form-control', 'id' => $tabla . '_' . $extraId)) }}
