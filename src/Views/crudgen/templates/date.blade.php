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
$format = "Y-m-d";
if (isset($datos["format"]["carbon"])) {
    $format = $datos["format"]["carbon"];
    $format = "Y-m-d";
} elseif (isset(trans("crudgenerator::admin.formats.carbon")["date"])) {
    $format = trans("crudgenerator::admin.formats.carbon.date");
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
$format = "YYYY-MM-DD";
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
    } else {
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
?>
<div class="form-group row {{$config['class_formgroup']}}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        {{ Form::text($extraId, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $extraId,$readonly)) }}
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>
<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
?>
<script>
     document.addEventListener("DOMContentLoaded", function () {
        $('#{{ $tabla . "_" . $extraId }}').datetimepicker({
            locale: '{{ App::getLocale() }}',
            format: '{{$format}}',
            inline: true,
            sideBySide: true,
            extraFormats: ["YYYY-MM-DD"],
        });
        $('#{{ $tabla . "_" . $extraId }}').closest("form").on('submit',function(e){
            var momento = $('#{{ $tabla . "_" . $extraId }}').data("DateTimePicker").date();
            $('#{{ $tabla . "_" . $extraId }}').val(momento.format("YYYY-MM-DD"));
        });
    });
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>