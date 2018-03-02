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
    $dato = $date->format($format);
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
?>
<div class="form-group row">
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
    $(document).ready(function () {
        $('#{{ $tabla . "_" . $extraId }}').datetimepicker({
            locale: '{{ App::getLocale() }}',
            inline: true,
            ignoreReadonly: false,
            format: '{{$format}}',
            sideBySide: true
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
