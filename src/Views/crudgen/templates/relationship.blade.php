<?php
$dato = old($columna);
if ($dato == "") {
    try {
        if (is_object($registro->{$columna})){
            $dato = $registro->{$columna}->getKey();
        }else{
            $dato = $registro->{$columna};
        }
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
    }else{
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
if (isset($datos["placeholder"])){
    $placeholder = $datos['placeholder'];
}else{
    $placeholder="";
}
$atributos = [
    'multiple' => 'multiple',
    'class' => 'form-control ' . $config['class_input'] . ' ' . $claseError,
    'id' => $tabla . '_' . $columna,
    //'placeholder' => $placeholder,
    $readonly
];
?>
<div class="form-group row">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}">
        {{ Form::select($columna, $datos["todos"], $dato, $atributos) }}
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
        $('#{{ $tabla . "_" . $columna }}').select2({
            minimumResultsForSearch: 8,
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