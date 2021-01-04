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
if (is_array($dato)){
    $dato = implode(array_get($datos, 'glue', '_'), $dato);
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
$extraClassDiv = array_get($datos, 'extraClassDiv', "");
$extraClassInput = array_get($datos, 'extraClassInput', "");
$extraDataInput = array_get($datos, 'extraDataInput', []);
$help = array_get($datos, 'help', "");
?>
<div class="form-group row {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        {{ Form::hidden($extraId, $valor_unchecked, array('class' => 'form-check-input ' . $claseError , 'id' => $tabla . '_' . $extraId . '_unchecked')) }}
        @if (is_array($datos['value']))
        <div class="card">
            <div class="card-body">
                @if (array_get($datos,'label', '') != '')
                {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 card-title' . $config['class_label']]) }}
                @endif
                @if (array_get($datos,'description', '') != '')
                <p class="card-text">
                    <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
                        {{ $datos['description'] }}
                    </small>
                </p>
                @endif
                @foreach($datos['value'] as $valor=>$datos2)
                <?php
                if (strpos($dato, $valor) === false) {
                    $checked = false;
                } else {
                    $checked = true;
                }
                $labelOpcion = $valor;
                $descriptionOpcion = "";
                $infoOpcion = "";
                $extraClassDiv = "";
                $extraClassInput = "";
                $extraDataInput = [];
                $separador = false;
                if (isset($datos2) && is_array($datos2)){
                    $separador = array_get($datos2, 'separador', $separador);
                    if ($separador){
                        $labelOpcion = "";
                    }
                    $labelOpcion = array_get($datos2, 'label', $labelOpcion);
                    $descriptionOpcion = array_get($datos2, 'description', $descriptionOpcion);
                    $infoOpcion = array_get($datos2, 'help', $infoOpcion);
                    $extraClassDiv = array_get($datos2, 'extraClassDiv', $extraClassDiv);
                    $extraClassInput = array_get($datos2, 'extraClassInput', $extraClassInput);
                    $extraDataInput = array_get($datos2, 'extraDataInput', []);
                    
                }elseif(isset($datos2) && is_string($datos2)){
                    $labelOpcion = $datos2;
                }
                $arrayDato = array_merge(
                    $extraDataInput,
                    [
                        'class' => "$claseError $extraClassInput" , 
                        'id' => $tabla . '_' . $extraId . '_' . $valor, 
                        $readonly
                    ]
                );
                ?>
                 @if ($separador)
                 <div class="mt-4 mb-3 {{ $extraClassDiv }}">
                     <hr class="mb-0">
                     @if ($labelOpcion != '')
                     {{ Form::label($tabla . '_' . $extraId . "_$valor", ucfirst($labelOpcion), ['class'=>"card-title {$config['class_label']} "]) }}
                     @endif
                     @if ($descriptionOpcion != '')
                     <p class="card-text">
                         <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId  . "_$valor"}}_help">
                             {{ $descriptionOpcion }}
                         </small>
                     </p>
                     @endif
                 </div>
                 @else
                <div class="input-group {{ ($infoOpcion != '') ? 'mb-0' : 'mb-3' }} {{ $extraClassDiv }}">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            {{ Form::radio($extraId . "[]", $valor, $checked, $arrayDato) }}
                        </div>
                    </div>
                    <label class='form-control overflow-auto ' for='{{$tabla . '_' . $extraId . '_' . $valor}}'>
                        {{ $labelOpcion }}
                    </label>
                    @if ($descriptionOpcion != "")
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <small class="text-muted" id="{{ $tabla . '_' . $extraId . '_' . $valor }}_help">
                                {{ $descriptionOpcion }}
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
                @if($infoOpcion != "")
                <small class="form-text text-muted {{ $extraClassDiv }} mt-0 mb-3">
                    {{ $infoOpcion }}
                </small>
                @endif
                @endif
                @endforeach
            </div>
        </div>
        @else
        <?php
        if ($datos['value'] == $dato || $dato == true) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <div class="input-group {{ ($help != '') ? 'mb-0' : 'mb-3' }}">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    {{ Form::radio($extraId, $datos['value'], $checked, array_merge(
                        $extraDataInput,
                        ['class' => " $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, $readonly])) }}
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
        @if($help != "")
        <small class="form-text text-muted {{ $help }} mt-0 mb-3">
            {{ $help }}
        </small>
        @endif
    </div>
</div>
<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
?>
<script>
    var {{ $tabla . "_" . $extraId }}Ejecutado = false;
    function {{ $tabla . "_" . $extraId }}Loader(){
        if (!{{ $tabla . "_" . $extraId }}Ejecutado){
            comenzarCheckeador();
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('chekeador_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>