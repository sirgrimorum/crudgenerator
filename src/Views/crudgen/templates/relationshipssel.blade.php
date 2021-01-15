<?php
$confirmTheme = config('sirgrimorum.crudgenerator.confirm_theme');
$confirmIcon = config('sirgrimorum.crudgenerator.icons.confirm');
$confirmContent = trans('crudgenerator::admin.messages.confirm_removepivot');
$confirmYes = trans('crudgenerator::admin.layout.labels.yes');
$confirmNo = trans('crudgenerator::admin.layout.labels.no');
$confirmTitle = trans('crudgenerator::admin.layout.labels.confirm_title');
if (isset($datos['extraId'])) {
    $extraId = $datos['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId);
if ($dato == "") {
    try {
        $dato = [];
        if ($registro) {
            foreach ($registro->{$columna}()->get() as $elemento) {
                $dato[$elemento->getKey()] = $elemento;
            }
        }
        //$dato = $registro->{$columna};
    } catch (Exception $ex) {
        $dato = "";
    }
} else {
    $dato = [];
    foreach (old($extraId) as $idAuxCampo => $idAuxDato) {
        $dato[$idAuxDato] = $datos["modelo"]::find($idAuxDato);
    }
}
if ($dato == "" && isset($datos["valor"])) {
    $dato = [];
    try {
        if (is_array($datos["valor"])) {
            foreach ($datos["valor"] as $idAuxDato) {
                $dato[$idAuxDato] = $datos["modelo"]::find($idAuxDato);
            }
        } else {
            $dato[$datos["valor"]] = $datos["modelo"]::find($datos["valor"]);
        }
    } catch (Exception $ex) {
        $dato = "";
    }
}
if ($dato == "") {
    $dato = [];
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($extraId)) {
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
$modeloOtro = strtolower(class_basename($datos['modelo']));
$modeloMio = strtolower(class_basename($config['modelo']));
$langGroup = "";
$listaOpciones = false;
if (isset($datos['groupby'])) {
    if (is_array($datos['groupby'])) {
        $buscarLang = $datos['groupby'][0];
    } else {
        $buscarLang = $datos['groupby'];
    }
    if (\Lang::has("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang)) {
        $langGroup = trans("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang);
    } else {
        $langGroup = trans('crudgenerator::admin.layout.all');
    }
    $listaTransOpciones = CrudGenerator::getOpcionesDeCampo($datos['modelo'], $datos['groupby']);
    $listaOpciones = CrudGenerator::getOpcionesDeCampo($datos['modelo'], $datos['groupby'], false);
    //echo "<pre>" . print_r($listaOpciones, true) . "</pre>";
}
if (is_array($datos['campo'])) {
    $camposQuery = json_encode($datos['campo']);
} else {
    $camposQuery = $datos['campo'];
}
$placeholder = \Illuminate\Support\Arr::get($datos, 'placeholder', "");
$extraClassDiv = \Illuminate\Support\Arr::get($datos, 'extraClassDiv', "");
$extraClassInput = \Illuminate\Support\Arr::get($datos, 'extraClassInput', "");
$extraDataInput = "";
foreach(\Illuminate\Support\Arr::get($datos, 'extraDataInput', []) as $extraAttribute => $extraAttributeData){
    $extraDataInput .= " {$extraAttribute}='$extraAttributeData'";
}
$help = \Illuminate\Support\Arr::get($datos, 'help', "");
?>
<div class="form-group row {{ $claseError }} {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }} " id="{{ $tabla . '_' . $extraId }}_container">
        <div class="typeahead__container {{ $claseError }}">
            <div class="typeahead__field">
                <span class="typeahead__query">
                    <input id="{{ $tabla . '_' . $extraId }}_search" name="{{ $tabla . '_' . $extraId }}_search[query]" class="{{ $extraClassInput }}" type="search" placeholder="{{ $placeholder }}" autocomplete="off" {{ $extraDataInput }}>
                </span>
                <span class="typeahead__button">
                    <button type="button" role="button">
                        <i class="typeahead__search-icon"></i>
                    </button>
                </span>

            </div>
        </div>
        <div data-pivote="principal"></div>


        @foreach($dato as $tablaInterId => $tablaInterCampo)
        <?php
        if (is_object($tablaInterCampo)) {
            $pivote = $tablaInterCampo->pivot;
        } else {
            $pivote = null;
        }
        if (is_object($pivote)) {
            if ($readonly == "") {
                $readonly = "";
            }
            $tablaOtroId = $tablaInterCampo->getKey();
        } elseif (!old($extraId)) {
            if (is_object($tablaInterCampo)) {
                $tablaOtroId = $tablaInterCampo->getKey();
                $readonly = "";
            } else {
                $readonly = "readonly";
                $tablaOtroId = 0;
            }
        } else {
            if (is_object($tablaInterCampo)) {
                $tablaOtroId = $tablaInterCampo->getKey();
                $readonly = "";
            } else {
                $readonly = "readonly";
                $tablaOtroId = 0;
            }
        }
        ?>
        @include('sirgrimorum::crudgen.templates.relationshipssel_item')
        @endforeach
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($extraId)[0] }}
        </div>
        @endif
        @if($help != "")
        <small class="form-text text-muted mt-0">
            {{ $help }}
        </small>
        @endif
    </div>
</div>
@include('sirgrimorum::crudgen.templates.relationshipssel_scripts')