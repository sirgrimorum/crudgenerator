<?php
$dato = old($columna);
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
    $dato = $date->format($format);
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
    }else{
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])){
    $readonly = $datos["readonly"];
}else{
    $readonly = "";
}
?>
<div class="form-group row">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        {{ Form::text($columna, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna,$readonly)) }}
        <small class="form-text text-muted" id="{{ $tabla . '_' . $columna }}_help">
            @if (isset($datos['description']))
            {{ $datos['description'] }}
            @endif
        </small>
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
    $(document).ready(function() {
        $('#{{ $tabla . "_" . $columna }}').datetimepicker({
            locale: '{{ App::getLocale() }}',
            format: '{{$format}}',
            inline: true,
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