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
if (isset($datos['multiple'])) {
    if ($datos['multiple'] == 'multiple') {
        $nomColumna = $columna . "[]";
        $arrayAttr = ['multiple' => 'multiple', 'class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna, $readonly];
    } else {
        $nomColumna = $columna;
        $arrayAttr = ['class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna, $readonly];
    }
} else {
    $nomColumna = $columna;
    $arrayAttr = ['class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $columna, $readonly];
}
//$arrayAttr["placeholder"]=$placeholder;
?>
<div class="form-group row">
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($columna, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
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
        $('#{{ $tabla . "_" . $columna }}').select2({
            minimumResultsForSearch: 8,
            width: '100%',
            language: "{{ App::getLocale()}}"
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