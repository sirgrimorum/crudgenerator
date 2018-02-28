<?php
$dato = old($columna);
if ($dato == "") {
    try {
        $dato = [];
        if ($registro) {
            foreach ($registro->{$columna}()->get() as $elemento) {
                $dato[$elemento->getKey()] = $elemento->pivot;
            }
        }
        //$dato = $registro->{$columna};
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
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
$modeloOtro = strtolower(class_basename($datos['modelo']));
$langGroup = "";
$listaOpciones=false;
if (isset($datos['groupby'])){
    if (is_array($datos['groupby'])){
        $buscarLang = $datos['groupby'][0];
    }else{
        $buscarLang = $datos['groupby'];
    }
    if (\Lang::has("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang)){
        $langGroup = trans("crudgenerator::" . $modeloOtro . ".labels." . $buscarLang);
    }else{
        $langGroup = trans('crudgenerator::admin.layout.all');
    }
    $listaTransOpciones = CrudLoader::getOpcionesDeCampo($datos['modelo'], $datos['groupby']);
    $listaOpciones = CrudLoader::getOpcionesDeCampo($datos['modelo'], $datos['groupby'],false);
    //echo "<pre>" . print_r($listaOpciones, true) . "</pre>";
}
if (is_array($datos['campo'])){
    $camposQuery = json_encode($datos['campo']);
}else{
    $camposQuery = $datos['campo'];
}
?>
@if (true)
<div class="form-group {{ $claseError }}">
    {{ Form::label($columna, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    <div class="{{ $config['class_divinput'] }}">
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
        <table class="table table-striped table-bordered" id='{{ $tabla . '_' . $columna }}'>
            <thead>
                <tr>
                    <td></td>
                    @foreach ($datos['columnas'] as $columnaT)
                    <td>{{ $columnaT['label'] }}</td>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($datos["todos"] as $tablaInterId => $tablaInterCampo)
                <?php
                $pivote = array_get($dato, $tablaInterId, false);
                if (is_object($pivote)) {
                    $checked = true;
                    if ($readonly == "") {
                        $readonly = "";
                    }
                } else {
                    $checked = false;
                    $readonly = "readonly";
                }
                ?>
                <tr>
                    <td>
                        {{ Form::checkbox($columna. "[" . $tablaInterId ."]", $tablaInterId, $checked, array('class' => 'chbx_'.$columna . ' ' . $claseError, 'id' => $columna . '_' . $tablaInterId)) }}
                    </td>
                    @foreach ($datos['columnas'] as $columnaT)
                    <?php
                    if ($columnaT['type'] == 'label') {
                        $valorM = CrudLoader::getNombreDeLista("", $tablaInterCampo);
                    } elseif (is_object($pivote)) {
                        if ($columnaT['type'] == 'labelpivot') {
                            $valorM = $pivote->{$columnaT['campo']};
                        } else {
                            $valorM = old($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId);
                            if ($valorM == "") {
                                try {
                                    $valorM = $pivote->{$columnaT['campo']};
                                } catch (Exception $ex) {
                                    $valorM = "";
                                }
                            }
                            if ($valorM == "") {
                                if (isset($columnaT["valor"])) {
                                    $valorM = $columnaT["valor"];
                                }
                            }
                        }
                    } else {
                        $valorM = $columnaT["valor"];
                    }
                    if (isset($columnaT["placeholder"])) {
                        $placeholder = $columnaT['placeholder'];
                    } else {
                        $placeholder = "";
                    }
                    ?>
                    @if ($columnaT['type']=='label')
                    <td>
                        {{ $valorM }}
                    </td>
                    @elseif ($columnaT['type']=='labelpivot')
                    <td>
                        {{ $valorM }}
                    </td>
                    @elseif ($columnaT['type']=='text')
                    <td>
                        {{ Form::text($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, $valorM, array('class' => 'form-control ' . $columna . '_' . $columnaT['campo'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, 'placeholder'=>$placeholder,$readonly)) }}
                    </td>
                    @elseif ($columnaT['type']=='textarea')
                    <td>
                        {{ Form::textarea($columna. "_" . $columnaT['campo'] . "_" . $tablaInterId, $valorM, array('class' => 'form-control ' . $columna . '_' . $columnaT['campo'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId,$readonly)) }}
                    </td>
                    @elseif ($columnaT['type']=='number')
                    <td>
                        {{ Form::number($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, $valorM, array('class' => 'form-control ' . $columna . '_' . $columnaT['campo'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, 'placeholder'=>$placeholder ,$readonly)) }}
                    </td>
                    @elseif ($columnaT['type']=='select')
                    <td>
                        {{ Form::select($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, $columnaT['opciones'], $valorM, array('class' => 'form-control ' . $columna . '_' . $columnaT['campo'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId,$readonly)) }}
                    </td>
                    @elseif ($columnaT['type']=='hidden')
                    {{ Form::hidden($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, $valorM, array('class' => 'form-control', 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId)) }}
                    @else
                    <td>
                        aqui{{ Form::text($columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, $valorM, array('class' => 'form-control ' . $columna . '_' . $columnaT['campo'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId, 'placeholder'=>$placeholder, $readonly)) }}
                    </td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
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
    $(".chbx_{{$columna}}").change(function() {
    @foreach($datos['columnas'] as $columnaT)
            @if ($columnaT['type'] != 'label' && $columnaT['type'] != 'labelpivot')
            var idTemp = "#" + "{{$columna}}_{{$columnaT['campo']}}_" + $(this).val();
    console.log(idTemp);
    $(idTemp).prop("readonly", !$(this).is(":checked"));
    if ($(idTemp).is("[readonly]")) {
    $(idTemp).val("");
    }
    @endif
            @endforeach
    });
    
    $.typeahead({
        input: '#{{ $tabla . '_' . $columna }}_search',
        minLength: 1,
        maxItem: 15,
        order: "asc",
        accent: true,
        searchOnFocus: true,
        //cache: true,
        @if(is_array($datos['campo']))
            <?php
            $auxTexto = "";
            $prefijoAuxTexto = "[";
            foreach($datos['campo'] as $auxTextocampo){
                $auxTexto .= $prefijoAuxTexto . "'" . $auxTextocampo . "'";
                $prefijoAuxTexto = ", ";
            }
            if ($auxTexto!=""){
                $auxTexto .= "]";
            }
            ?>
            display: {!!$auxTexto!!},
        @else
            display: ["{!!$datos['campo']!!}"],
        @endif
        hint: true,
        @if(isset($datos['groupby']))
        group: {
            template: "@{{group}}"
        },
        dropdownFilter: "{{ $langGroup }}",
        //href: "/beers/@{{group|slugify}}/@{{display|slugify}}/",
        @else
        //href: "/beers/@{{group|slugify}}/@{{display|slugify}}/",
        @endif
        maxItemPerGroup: 4,
        backdrop: {
            "background-color": "#fff"
        },
        emptyTemplate: 'No result for "@{{query}}"',
        source: {
            @if (isset($datos['groupby']))
            @if($listaOpciones)
               @foreach($listaOpciones as $indice=>$opcionGroup)
            "{{CrudLoader::getNombreDeLista('',$listaTransOpciones[$indice])}}": {
                ajax: {
                    url: "{!! route('sirgrimorum_modelos::index',['localecode'=>App::getLocale(),'modelo'=>$modeloOtro]) !!}?_return=pureJson&_q={{CrudLoader::getNombreDeLista('',$opcionGroup,'|')}}&_a={{CrudLoader::getNombreDeLista('',$datos['groupby'],'|')}}&_aByA=1&_or=false",
                    path: "result"
                }
            },               
                @endforeach
            @else
                ajax: {
                    //url: "{!! route('sirgrimorum_modelos::index',['localecode'=>App::getLocale(),'modelo'=>$modeloOtro]) !!}?_return=pureJson&_q=@{{query}}*%&_a={{$camposQuery}}",
                    url: "{!! route('sirgrimorum_modelos::index',['localecode'=>App::getLocale(),'modelo'=>$modeloOtro]) !!}?_return=pureJson",
                    path: "result"
                }
            @endif
            @else
                ajax: {
                    //url: "{!! route('sirgrimorum_modelos::index',['localecode'=>App::getLocale(),'modelo'=>$modeloOtro]) !!}?_return=pureJson&_q=@{{query}}*%&_a={{$camposQuery}}",
                    url: "{!! route('sirgrimorum_modelos::index',['localecode'=>App::getLocale(),'modelo'=>$modeloOtro]) !!}?_return=pureJson",
                    path: "result"
                }
            @endif
        },
        callback: {
            onClickAfter: function (node, a, item) {
                console.log(item);
            }
        },
        debug: true
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
@else
<pre>{!! print_r($datos['todos'],true)!!}</pre>
@endif