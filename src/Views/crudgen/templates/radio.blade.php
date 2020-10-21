<?php
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId) ?? "";
if ($dato === "") {
    try {
        $dato = $registro->{$columna};
    } catch (Exception $ex) {
        $dato = "";
    }
}
if ($dato === "") {
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
$valor_unchecked = 0;
if (isset($datos['unchecked'])){
    $valor_unchecked = $datos['unchecked'];
}
if (isset($datos["readonly"])) {
    if ($datos["readonly"] == "readonly"){
        $readonly = "disabled='disabled'";
    }else{
        $readonly = $datos["readonly"];
    }
} else {
    $readonly = "";
}
?>
<div class="form-group row {{$config['class_formgroup']}}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        {{ Form::hidden($extraId, $valor_unchecked, array('class' => 'form-check-input ' . $claseError , 'id' => $tabla . '_' . $extraId . '_unchecked')) }}
        @if (is_array($datos['valor']))
        @foreach($datos['valor'] as $valor=>$datos2)
        <?php
        if (stripos($valor, $dato) === false) {
            $checked = false;
        } else {
            $checked = true;
        }
        ?>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {{ Form::radio($extraId, $valor, array('class' => 'form-check-input ' .$claseError, 'id' => $tabla . '_' . $extraId . '_' . $valor, $readonly),$checked) }}
                </div>
            </div>
            <label class='form-control' for='{{$tabla . '_' . $extraId . '_' . $valor}}'>
                {{ $datos2['label'] }}
            </label>
            @if (isset($datos2['description']))
            <div class="input-group-append">
                <div class="input-group-text">
                    <small class="text-muted" id="{{ $tabla . '_' . $extraId . '_' . $valor }}_help">
                        {{ $datos2['description'] }}
                    </small>
                </div>
            </div>
            @endif
        </div>
        @endforeach
        @else
        <?php
        if ($datos['valor'] == $dato) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {{ Form::radio($extraId, $datos['valor'], array('class' => 'form-check-input '  . $claseError, 'id' => $tabla . '_' . $extraId, $readonly),$checked) }}
                </div>
            </div>
            <label class='form-control' for='{{$tabla . '_' . $extraId }}'>
                {{ $datos['label'] }}
            </label>
            @if (isset($datos['description']))
            <div class="input-group-append">
                <div class="input-group-text">
                    <small class="text-muted" id="{{ $tabla . '_' . $extraId }}_help">
                        {{ $datos['description'] }}
                    </small>
                </div>
            </div>
            @endif
        </div>
        @endif
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>
