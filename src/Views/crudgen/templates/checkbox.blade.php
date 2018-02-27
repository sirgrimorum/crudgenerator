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
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {{ Form::checkbox($columna, $valor, $checked, array('class' => 'form-check-input ' . $claseError , 'id' => $tabla . '_' . $columna . '_' . $valor)) }}
                </div>
            </div>
            <label class='form-control' for='{{$tabla . '_' . $columna . '_' . $valor}}'>
                {{ $datos2['label'] }}
            </label>
            @if (isset($datos2['description']))
            <div class="input-group-append">
                <div class="input-group-text">
                    <small class="text-muted" id="{{ $tabla . '_' . $columna . '_' . $valor }}_help">
                        {{ $datos2['description'] }}
                    </small>
                </div>
            </div>
            @endif
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
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {{ Form::checkbox($columna, $datos['value'], $checked, array('class' => ' '. $claseError, 'id' => $tabla . '_' . $columna)) }}
                </div>
            </div>
            <label class='form-control' for='{{$tabla . '_' . $columna }}'>
                {{ $datos['label'] }}
            </label>
            @if (isset($datos['description']))
            <div class="input-group-append">
                <div class="input-group-text">
                    <small class="text-muted" id="{{ $tabla . '_' . $columna }}_help">
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