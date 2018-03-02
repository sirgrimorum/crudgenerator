<?php
$dato = $datos['modelo']::where($tabla . "." . $datos['id'], "=", $datoId)->get();

if ($dato == "") {
    $dato = [];
}

$error_campo = false;
$claseError = '';
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
$modeloOtro = strtolower(class_basename($datos['modelo']));
$langGroup = "";
$listaOpciones = false;
if (isset($datos['groupby'])) {
    if (is_array($datos['groupby'])) {
        $buscarLang = $datos['groupby'][0];
    } else {
        $buscarLang = $datos['groupby'];
    }
    if (\Lang::has("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang)) {
        $langGroup = trans("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang);
    } else {
        $langGroup = trans('crudgenerator::admin.layout.all');
    }
    $listaTransOpciones = CrudGenerator::getOpcionesDeCampo($datos['modelo'], $datos['groupby']);
    $listaOpciones = CrudGenerator::getOpcionesDeCampo($datos['modelo'], $datos['groupby'], false);
    //echo "<pre>" . print_r($listaOpciones, true) . "</pre>";
}
if (is_array($datos['campo'])) {
    $camposQuery = json_encode($datos['campo']);
} else {
    $camposQuery = $datos['campo'];
}
$readonly = "";
?>
@foreach($dato as $tablaInterId => $tablaInterCampo)
<?php
$pivote = $tablaInterCampo->pivot;
$readonly = "";
$tablaOtroId = $datoId;
?>
@include('sirgrimorum::crudgen.templates.relationshipssel_item')
@endforeach
