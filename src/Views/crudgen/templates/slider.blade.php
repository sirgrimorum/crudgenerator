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
if (isset($datos["min"])) {
    $min = $datos["min"];
} else {
    $min = 0;
}
if (isset($datos["max"])) {
    $max = $datos["max"];
} else {
    $max = 100;
}
if (isset($datos["step"])) {
    $step = $datos["step"];
} else {
    $step = 1;
}
if (isset($datos["post"])) {
    $post = $datos["post"];
} else {
    $post = "";
}
if (isset($datos["pre"])) {
    $pre = $datos["pre"];
} else {
    $pre = "";
}
$extraClassDiv = \Illuminate\Support\Arr::get($datos, 'extraClassDiv', "");
$extraClassInput = \Illuminate\Support\Arr::get($datos, 'extraClassInput', "");
$extraDataInput = \Illuminate\Support\Arr::get($datos, 'extraDataInput', []);
$help = \Illuminate\Support\Arr::get($datos, 'help', "");
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
        {{ Form::text($extraId, $dato, array_merge(
            $extraDataInput,
            ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, 'data-slider-id'=>$tabla . '_' . $extraId . 'Slider', 'data-slider-min'=>$min, 'data-slider-max'=>$max, 'data-slider-step'=>$step, 'data-slider-value'=>$dato ,$readonly])) }}
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
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
            $('#{{ $tabla . "_" . $extraId }}').sliderb({
                formatter: function (value) {
                    return "{{ $pre }}" + value + "{{ $post }}";
                }
            });
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('bootstrap-slider_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>
