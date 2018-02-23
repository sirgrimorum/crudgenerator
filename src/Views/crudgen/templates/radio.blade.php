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
    }else{
        $claseError = 'is-valid';
    }
}
?>
<div class="form-group row">
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        @if (is_array($datos['valor']))
        @foreach($datos['valor'] as $valor=>$datos2)
        <?php
        if (stripos($valor, $dato) === false) {
            $checked = false;
        } else {
            $checked = true;
        }
        ?>
        <div class="form-check">
            {{ Form::radio($columna, $valor, array('class' => 'form-check-input ' .$claseError, 'id' => $tabla . '_' . $columna . '_' . $valor),$checked) }}
            <label class='form-check-label' for='{{$tabla . '_' . $columna . '_' . $valor}}'>
                {{ $datos2['label'] }}
            </label>
            <small class="text-muted" id="{{ $tabla . '_' . $columna . '_' . $valor }}_help">
                @if (isset($datos2['description']))
                {{ $datos2['description'] }}
                @endif
            </small>
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
        <div class="radio">
            {{ Form::radio($columna, $datos['valor'], array('class' => 'form-check-input '  . $claseError, 'id' => $tabla . '_' . $columna),$checked) }}
            <label class='form-check-label' for='{{$tabla . '_' . $columna }}'>
                {{ $datos['label'] }}
            </label>
            <small class="text-muted" id="{{ $tabla . '_' . $columna }}_help">
                @if (isset($datos['description']))
                {{ $datos['description'] }}
                @endif
            </small>
        </div>
        @endif
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>