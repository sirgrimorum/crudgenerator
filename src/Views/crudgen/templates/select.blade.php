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
if (isset($datos['multiple'])) {
    if ($datos['multiple'] == 'multiple') {
        $nomColumna = $extraId . "[]";
        $arrayAttr = ['multiple' => 'multiple', 'class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, $readonly];
    } else {
        $nomColumna = $extraId;
        $arrayAttr = ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, $readonly];
    }
} else {
    $nomColumna = $extraId;
    $arrayAttr = ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, $readonly];
}
$arrayAttr = array_merge($extraDataInput,$arrayAttr);

//$arrayAttr["placeholder"]=$placeholder;
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
        {{ Form::select($nomColumna, $datos['opciones'], $dato, $arrayAttr) }}
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
        @if($help != "")
        <small class="form-text text-muted {{ $help }} mt-0">
            {{ $help }}
        </small>
        @endif
    </div>
</div>
<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
?>
<script>
    var {{ $tabla . "_" . $extraId }}Ejecutado = false;
    function {{ $tabla . "_" . $extraId }}Loader(){
        if (!{{ $tabla . "_" . $extraId }}Ejecutado){
            $('#{{ $tabla . "_" . $extraId }}').select2({
                minimumResultsForSearch: 8,
                width: '100%',
                language: "{{ App::getLocale()}}"
            });
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    var {{ $tabla . "_" . $extraId }}_selecteador_Ejecutado = false;
    function {{ $tabla . "_" . $extraId }}_selecteador_Loader(){
        if (!{{ $tabla . "_" . $extraId }}_selecteador_Ejecutado){
            comenzarCheckeador();
        }
        {{ $tabla . "_" . $extraId }}_selecteador_Ejecutado = true;
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
        {{ $tabla . "_" . $extraId }}_selecteador_Loader();
    });
    {{ $nameScriptLoader }}('select2_min_js',"{{ $tabla . "_" . $extraId }}Loader();");
    {{ $nameScriptLoader }}('checkeador_js',"{{ $tabla . "_" . $extraId }}_selecteador_Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>