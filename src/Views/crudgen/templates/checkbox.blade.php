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
        @if (is_array($datos['value']))
        @foreach($datos['value'] as $valor=>$datos2)
        <?php
        if (stripos($valor, $dato) === false) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <div class="form-check">
            {{ Form::checkbox($columna, $valor, $checked, array('class' => 'form-check-input ' . $claseError , 'id' => $tabla . '_' . $columna . '_' . $valor)) }}
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
        if ($datos['value'] == $dato) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <div class="form-check">
            {{ Form::checkbox($columna, $datos['value'], $checked, array('class' => 'form-check-input '. $claseError, 'id' => $tabla . '_' . $columna)) }}
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