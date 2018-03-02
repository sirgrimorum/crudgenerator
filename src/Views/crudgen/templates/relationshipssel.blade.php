<?php
$confirmTheme = config('sirgrimorum.crudgenerator.confirm_theme');
$confirmIcon = config('sirgrimorum.crudgenerator.confirm_icon');
$confirmContent = trans('crudgenerator::admin.messages.confirm_removepivot');
$confirmYes = trans('crudgenerator::admin.layout.labels.yes');
$confirmNo = trans('crudgenerator::admin.layout.labels.no');
$confirmTitle = trans('crudgenerator::admin.layout.labels.confirm_title');
$dato = old($columna);
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
    foreach (old($columna) as $idAuxDato => $idAuxCampo) {
        $dato[$idAuxDato] = $datos["modelo"]::find($idAuxDato);
    }
}
if ($dato == "") {
    $dato = [];
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
?>
<div class="form-group row {{ $claseError }}">
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($columna, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}" id="{{ $tabla . '_' . $columna }}_container">
        <div class="typeahead__container">
            <div class="typeahead__field">
                <span class="typeahead__query">
                    <input id="{{ $tabla . '_' . $columna }}_search" name="{{ $tabla . '_' . $columna }}_search[query]" type="search" placeholder="Search" autocomplete="off">
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
        } elseif (!old($columna)) {
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
    </div>

    @if ($error_campo)
    <div class="invalid-feedback">
        {{ $errors->get($columna) }}
    </div>
    @endif
</div>
@include('sirgrimorum::crudgen.templates.relationshipssel_scripts')