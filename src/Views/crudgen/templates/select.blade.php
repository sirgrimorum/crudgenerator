<?php
if (isset($datos['extraId'])) {
    $extraId = $datos['extraId'];
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
    if ($errors->has($extraId)) {
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
$extraClassDiv = \Illuminate\Support\Arr::get($datos, 'extraClassDiv', "");
$extraClassInput = \Illuminate\Support\Arr::get($datos, 'extraClassInput', "");
$extraDataInput = \Illuminate\Support\Arr::get($datos, 'extraDataInput', []);
$help = \Illuminate\Support\Arr::get($datos, 'help', "");
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
if (is_callable($datos['opciones'])){
    if (isset($registroPadre)){
        $opciones = $datos['opciones']($registroPadre);
    }else{
        $opciones = $datos['opciones']();
    }
    if (!is_array($opciones)){
        $opciones = [];
    }
}elseif(is_array($datos['opciones'])){
    $opciones = $datos['opciones'];
}else{
    $opciones = [];
}
if (!isset($datos['multiple'])){
    $opciones = array_merge([""=>$placeholder], $opciones);
}elseif(is_string($dato) && CrudGenerator::isJsonString($dato)){
    $dato = json_decode($dato);
}
if (stripos(\Illuminate\Support\Arr::get($config,"rules.{$columna}", ""), "required")!==false){
    data_set($arrayAttr,'required',"required");
}
?>
<div class="form-group row {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo'
    data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        {{ Form::select($nomColumna, $opciones, $dato, $arrayAttr) }}
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($extraId)[0] }}
        </div>
        @endif
        @if($help != "")
        <small class="form-text text-muted mt-0">
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