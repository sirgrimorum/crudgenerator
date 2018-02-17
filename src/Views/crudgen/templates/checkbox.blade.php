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
        $claseError = 'has-error';
    } 
}
?>
<div class="form-group {{ $claseError }}">
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
        <div class="checkbox">
            <label>
                {{ Form::checkbox($columna, $valor, $checked, array('class' => '', 'id' => $tabla . '_' . $columna . '_' . $valor)) }}
                {{ $datos2['label'] }}
            </label>
            <span class="help-block" id="{{ $tabla . '_' . $columna . '_' . $valor }}_help">
                @if (isset($datos2['description']))
                {{ $datos2['description'] }}
                @endif
            </span>
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
        <div class="checkbox">
            <label>
                {{ Form::checkbox($columna, $datos['value'], $checked, array('class' => '', 'id' => $tabla . '_' . $columna)) }}
                {{ $datos['label'] }}
            </label>
            <span class="help-block" id="{{ $tabla . '_' . $columna }}_help">
                @if (isset($datos['description']))
                {{ $datos['description'] }}
                @endif
            </span>
            
        </div>
        @endif
    </div>
</div>