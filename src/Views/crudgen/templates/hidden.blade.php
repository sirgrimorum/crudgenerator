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
$extraClassInput = array_get($datos, 'extraClassInput', "");
$extraDataInput = array_get($datos, 'extraDataInput', []);
?>
{{ Form::hidden($extraId, $dato, array_merge(
    $extraDataInput,
    ['class' => "form-control $extraClassInput", 'id' => $tabla . '_' . $extraId])) }}
