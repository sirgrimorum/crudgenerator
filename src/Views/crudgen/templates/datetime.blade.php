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
$format = "Y-m-d H:i:s";
if (isset($datos["format"]["carbon"])) {
    $format = $datos["format"]["carbon"];
    $format = "Y-m-d H:i:s";
} elseif (isset(trans("crudgenerator::admin.formats.carbon")["datetime"])) {
    $format = trans("crudgenerator::admin.formats.carbon.datetime");
}
if ($dato != "") {
    if (isset($datos["timezone"])) {
        $timezone = $datos["timezone"];
    } else {
        $timezone = config("app.timezone");
    }
    $date = new \Carbon\Carbon($dato, $timezone);
    if (stripos($format, "%")!==false){
        setlocale(LC_TIME, App::getLocale());
        Carbon\Carbon::setUtf8(true);
        $dato = $date->formatLocalized($format);
    }else{
        $dato = $date->format($format);
    }
}
$format = "YYYY-MM-DD HH:mm:ss";
if (isset($datos["format"]["moment"])) {
    $format = $datos["format"]["moment"];
} elseif (isset(trans("crudgenerator::admin.formats.moment")["datetime"])) {
    $format = trans("crudgenerator::admin.formats.moment.datetime");
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($columna)) {
        $error_campo = true;
        $claseError = 'is-invalid';
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
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
            ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId,$readonly])) }}
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
            $('#{{ $tabla . "_" . $extraId }}').datetimepicker({
                locale: '{{ App::getLocale() }}',
                inline: true,
                ignoreReadonly: false,
                format: '{{$format}}',
                sideBySide: true,
                extraFormats: ["YYYY-MM-DD HH:mm:ss"],
            });
            $('#{{ $tabla . "_" . $extraId }}').closest("form").on('submit',function(e){
                var momento = $('#{{ $tabla . "_" . $extraId }}').data("DateTimePicker").date();
                $('#{{ $tabla . "_" . $extraId }}').val(momento.format("YYYY-MM-DD HH:mm:ss"));
            });
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('bootstrap-datetimepicker_min_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>
