<?php
if (!isset($action)) {
    $action = substr(request()->route()->getName(), stripos(request()->route()->getName(), "::") + 2);
}
$tabla = $config['tabla'];
$campos = $config['campos'];
$botones = $config['botones'];
if (isset($config['files'])) {
    $files = $config['files'];
} else {
    $files = false;
}
if (isset($config['relaciones'])) {
    $relaciones = $config['relaciones'];
}
$url = $config['url'];
if (!isset($config['class_form'])) {
    $config['class_form'] = '';
}
if (!isset($config['class_formgroup'])) {
    $config['class_formgroup'] = '';
}
if (!isset($config['class_labelcont'])) {
    $config['class_labelcont'] = 'col-xs-12 col-sm-3 col-md-2';
}
if (!isset($config['class_label'])) {
    $config['class_label'] = 'col-form-label font-weight-bold mb-0 pb-0';
}
if (!isset($config['class_divinput'])) {
    $config['class_divinput'] = 'col-xs-12 col-sm-8 col-md-10';
}
if (!isset($config['class_input'])) {
    $config['class_input'] = '';
}
if (!isset($config['class_offset'])) {
    $config['class_offset'] = 'offset-xs-0 offset-sm-4 offset-md-2';
}
if (!isset($config['class_button'])) {
    $config['class_button'] = 'btn btn-primary';
}
if (isset($datos['extraId'])) {
    $extraId = $datos['extraId'];
} else {
    $extraId = $columna;
}
$auxCampos = [];
foreach($datos['columnas'] as $indexCampos => $columnaT){
    if (isset($columnaT['campo'])){
        $campoName = $columnaT['campo'];
    }else{
        $campoName = $datos['campo'] . "_$indexCampos";
    }
    $auxCampos[$campoName] = $columnaT;
    if (!isset($columnaT['tipo']) && isset($columnaT['type'])){
        $columnaT['tipo'] = $columnaT['type'];
        $auxCampos[$campoName]['tipo'] = $columnaT['type'];
    }
    if ( $columnaT['tipo'] != 'label') {
        $auxCampos[$campoName]['extraId'] = $extraId . "_" . $campoName . "_" . $tablaOtroId;
    } else {
        $auxCampos[$campoName]['extraId'] = $extraId . "_" . "_" . $tablaOtroId;
    }
}
$auxConfigParaIncludes = [
    "tabla" => $tabla,
    "campos" => $auxCampos
];
?>
@include("sirgrimorum::crudgen.partials.includes", [
    'config' => $auxConfigParaIncludes,
    'tieneHtml' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['html', 'article']),
    'tieneDate' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['date', 'datetime', 'time']),
    'tieneSlider' => CrudGenerator::hasTipo($auxConfigParaIncludes, 'slider'),
    'tieneSelect' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['select', 'relationship', 'relationships']),
    'tieneSearch' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['relationshipssel']),
    'tieneColor' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['color']),
    'tieneCheckeador' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['select', 'checkbox', 'radio']),
    'tieneFile' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['file', 'files']),
    'tieneJson' => CrudGenerator::hasTipo($auxConfigParaIncludes, ['json']),
    'tieneInputFilter' => CrudGenerator::hasClave($auxConfigParaIncludes, 'inputfilter'),
    'js_section' => "",
    'css_section' => "",
    'modelo' => $datos['modelo']
])
@foreach ($auxCampos as $columnaName => $columnaT)
{{-- 1. no es oculto para esta acción --}}
@if ((($action == "create" && !isset($columnaT['nodb'])) || $action != "create") && CrudGenerator::inside_array($columnaT, "hide", $action) === false)
<?php
$extraIdInner = $columnaT['extraId'];
if ($columnaT['tipo'] == 'label') {
    if (isset($columnaT['campo'])) {
        $valorM = CrudGenerator::getNombreDeLista($tablaInterCampo, $columnaT['campo']);
    } else {
        $valorM = CrudGenerator::getNombreDeLista($tablaInterCampo, $datos['campo']);
    }
} elseif (is_object($pivote)) {
    if ($columnaT['tipo'] == 'labelpivot') {
        $valorM = $pivote->{$columnaT['campo']};
    } else {
        $valorM = old($extraIdInner);
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
if (isset($datos["card_class"])) {
    $card_class = $datos['card_class'];
} else {
    $card_class = "";
}
$extraClassInput = array_get($datos, 'extraClassInput', "");
$atributos = [
    'class' => "form-control $claseError $extraClassInput",
    'placeholder' => $placeholder,
    'style' => "max-height:100px;",
    $readonly
];
if (isset($columnaT['campo']) && $columnaT['tipo'] != 'label') {
    $atributos['class'] .= ' ' . $extraId . '_' . $columnaT['campo'];
    $atributos['id'] = $extraIdInner;
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
?>
{{-- 2. Si es la primera vuelta --}}
@if ($loop->first)
<div class="card mb-1 mt-1 {{$card_class}}" id="{{$extraId . "_" . $tablaOtroId}}_principal" data-pivote="principal">
    {{ Form::hidden($extraId. "[" . $valorM ."]", $tablaOtroId, array('class' => 'form-control', 'id' => $extraId . '_' . $tablaOtroId)) }}
    <div class="card-header text-center">
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group" aria-label="Third group">
                <button type="button" class="btn btn-dark" data-toggle="collapse" href="#{{ $tabla . '_' . $extraId . "_" . $tablaOtroId}}_info" title='{{ trans("crudgenerator::admin.layout.labels.info")}}'>{!! CrudGenerator::getIcon('info',true) !!}</button>
            </div>
            <span class="card-title mb-2 mt-2 mr-1 ml-1"><!--small>{{ $columnaT['label'] }}</small><br-->{{ $valorM }}</span>
            <div class="btn-group" role="group" aria-label="Third group">
                <button type="button" class="btn btn-danger" onclick="quitarPivote('{{$extraId . "_" . $tablaOtroId}}_principal','{{ $valorM }}')" title="{{trans("crudgenerator::admin.layout.labels.remove")}}">{!! CrudGenerator::getIcon('minus',true,'fa-lg') !!}</button>
            </div>
        </div>
    </div>
    {{-- 3. Si no es la última --}}
    @if(!$loop->last)
    <div class="card-body">
        <div class="">
            {{-- 3. el de si es la última --}}
            @endif
            {{-- 2. no es la primera --}}
            @else
            {{-- 4. Si es label o label pivote --}}
            @if ($columnaT['tipo']=='label' || $columnaT['tipo']=='labelpivot')
            @if(isset($columnaT['pre_html']))
            {!! $columnaT['pre_html'] !!}
            @endif
            <div class="form-group row">
                <div class='{{$config['class_labelcont']}}'>
                    {{ Form::label($extraIdInner, ucfirst($columnaT['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
                    @if (isset($columnaT['description']))
                    <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraIdInner }}_help">
                        {{ $columnaT['description'] }}
                    </small>
                    @endif
                </div>
                <div class="{{ $config['class_divinput'] }}">
                    {{ Form::text($extraIdInner, $valorM, array('class' => "form-control-plaintext {$config['class_input']} $claseError $extraClassInput", 'id' => $extraIdInner, "readonly"=>"readonly")) }}
                </div>
            </div>
            @if(isset($columnaT['post_html']))
            {!! $columnaT['post_html'] !!}
            @endif
            {{-- 4. No es label ni label pivote --}}
            @else
            @include("sirgrimorum::crudgen.partials.create_inner",[
                "js_section" => "",
                'css_section'=> "", 
                'modelo'=> $datos['modelo'], 
                'action'=> $action,
                "tabla" => $tabla,
                "config" => $config,
                "columna" => $columnaT['campo'],
                "datos" => $columnaT,
                "registro" => $pivote, 
                "errores" => count($errors->all())>0,
                "nameScriptLoader" => $nameScriptLoader,
            ])
            {{-- 4. el de si es label o label pivote --}}
            @endif
            {{-- 2. El de si es la primera --}}
            @endif
            {{-- 5. Si es la última --}}
            @if($loop->last)
            {{-- 6. Si no es la primera vuelta --}}
            @if(!$loop->first)
        </div>
    </div>
    {{-- 6. la de si no es la primera --}}
    @endif
    <div class="collapse" id='{{ $tabla . '_' . $extraId . "_" . $tablaOtroId}}_info'>
        <div class='card-footer text-muted'>
            @include("sirgrimorum::admin.show.simple", ["modelo" => class_basename($datos["modelo"]),"config" => \Illuminate\Support\Arr::get($datos, 'config', ""),"registro"=>$tablaOtroId])
        </div>
    </div>
</div>
{{-- 5. La de si es la última --}}
@endif
{{-- 1. El de si está oculto para esta acción --}}
@endif
@endforeach