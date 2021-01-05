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
$locked = isset($datos["value"]) ? "data-locked='si'" : "data-locked='no'";
if ($dato == "") {
    if (isset($datos["valor"])) {
        if (is_array($datos["valor"]) || is_object($datos["valor"])){
            $dato = json_encode($datos["valor"]);
        }else{
            $dato = $datos["valor"];
        }
    }elseif(isset($datos["value"])){
        if (is_array($datos["value"]) || is_object($datos["value"])){
            $dato = json_encode($datos["value"]);
        }else{
            $dato = $datos["value"];
        }
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
$extraClassDiv = array_get($datos, 'extraClassDiv', "");
$extraClassInput = array_get($datos, 'extraClassInput', "");
$extraDataInput = array_get($datos, 'extraDataInput', []);
$help = array_get($datos, 'help', "");
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
        <div class="w-100 m-0" id="contenedor_json_{{ $tabla . "_" . $extraId }}"></div>
        {{ Form::textarea($extraId, $dato, array_merge(
            $extraDataInput,
            ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId,$readonly, $locked])) }}
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
        <button class="btn btn-secondary mt-2" role="button" type="button" onclick="{{ $tabla . "_" . $extraId }}Loader();prettyPrint('{{$tabla . '_' . $extraId}}')">{{trans("crudgenerator::admin.layout.labels.pretty_print")}}</button>
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
    var json_{{ $tabla . "_" . $extraId }};
    function {{ $tabla . "_" . $extraId }}Loader(){
        if (!{{ $tabla . "_" . $extraId }}Ejecutado){
            var jsonInicial = "{}";
            try {
                var ugly = document.getElementById('{{ $tabla . "_" . $extraId }}').value;
                var obj = JSON.parse(ugly);
                jsonInicial = ugly;
            }catch(err) {
                console.log('error leyendo json', err);
            }
            json_{{ $tabla . "_" . $extraId }} = new JSONedtr(jsonInicial, '#contenedor_json_{{ $tabla . "_" . $extraId }}',{
                'instantChange' : true,
                'runFunctionOnUpdate' : 'json_{{ $tabla . "_" . $extraId }}_onChange',
                'locked' : $('#{{ $tabla . "_" . $extraId }}').data('locked') == 'si'
            });
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    function json_{{ $tabla . "_" . $extraId }}_onChange(data){
        console.log('llega', data.getDataString());
        $('#{{ $tabla . "_" . $extraId }}').val(data.getDataString());
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('JSONedtr_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>