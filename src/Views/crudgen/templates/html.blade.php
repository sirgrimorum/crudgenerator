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
        {{ Form::textarea($extraId, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $extraId,$readonly)) }}
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
        CKEDITOR.replace('{{ $tabla . "_" . $extraId }}');
    })();
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>