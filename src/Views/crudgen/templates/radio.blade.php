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
        @if (is_array($datos['valor']))
        @foreach($datos['valor'] as $valor=>$datos2)
        <?php
        if (stripos($valor, $dato) === false) {
            $checked = false;
        } else {
            $checked = true;
        }
        ?>
        <div class="radio">
            <label>
                {{ Form::radio($columna, $valor, array('class' => '', 'id' => $tabla . '_' . $columna . '_' . $valor),$checked) }}
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
        if ($datos['valor'] == $dato) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <div class="radio">
            <label>
                {{ Form::radio($columna, $datos['valor'], array('class' => '', 'id' => $tabla . '_' . $columna),$checked) }}
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