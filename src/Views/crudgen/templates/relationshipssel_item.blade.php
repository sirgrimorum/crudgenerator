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
?>
@foreach ($datos['columnas'] as $columnaT)
@if (CrudGenerator::inside_array($columnaT, "hide", $action) === false)
<?php
if ($columnaT['type'] == 'label') {
    if (isset($columnaT['campo'])) {
        $valorM = CrudGenerator::getNombreDeLista($tablaInterCampo, $columnaT['campo']);
    } else {
        $valorM = CrudGenerator::getNombreDeLista($tablaInterCampo, $datos['campo']);
    }
} elseif (is_object($pivote)) {
    if ($columnaT['type'] == 'labelpivot') {
        $valorM = $pivote->{$columnaT['campo']};
    } else {
        $valorM = old($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId);
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
$atributos = [
    'class' => 'form-control ' . $claseError,
    'placeholder' => $placeholder,
    'style' => "max-height:100px;",
    $readonly
];
if (isset($columnaT['campo']) && $columnaT['type'] != 'label') {
    $atributos['class'] .= ' ' . $columna . '_' . $columnaT['campo'];
    $atributos['id'] = $columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId;
    $config['extraId'] = $columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId;
} else {
    $config['extraId'] = $columna . "_" . "_" . $tablaOtroId;
}
?>
@if ($loop->first)
<div class="card mb-1 mt-1 {{$card_class}}" id="{{$columna . "_" . $tablaOtroId}}_principal" data-pivote="principal">
    {{ Form::hidden($columna. "[" . $tablaOtroId ."]", $tablaOtroId, array('class' => 'form-control', 'id' => $columna . '_' . $tablaOtroId)) }}
    <div class="card-header text-center">
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group" aria-label="Third group">
                <button type="button" class="btn btn-dark" data-toggle="collapse" href="#{{ $tabla . '_' . $columna . "_" . $tablaOtroId}}_info" title='{{ trans("crudgenerator::admin.layout.labels.info")}}'><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i></button>
            </div>
            <span class="card-title mb-2 mt-2 mr-1 ml-1"><!--small>{{ $columnaT['label'] }}</small><br-->{{ $valorM }}</span>
            <div class="btn-group" role="group" aria-label="Third group">
                <button type="button" class="btn btn-danger" onclick="quitarPivote('{{$columna . "_" . $tablaOtroId}}_principal','{{ $valorM }}')" title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus fa-lg" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
    @if(!$loop->last)
    <div class="card-body">

        <div class="">
            @endif
            @else
            @if(isset($columnaT['pre_html']))
            {!! $columnaT['pre_html'] !!}
            @endif
            @if ($columnaT['type']=='label' || $columnaT['type']=='labelpivot')
            <div class="form-group row">
                <div class='{{$config['class_labelcont']}}'>
                    {{ Form::label($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, ucfirst($columnaT['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
                    @if (isset($columnaT['description']))
                    <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId }}_help">
                        {{ $columnaT['description'] }}
                    </small>
                    @endif
                </div>
                <div class="{{ $config['class_divinput'] }}">
                    {{ Form::text($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, array('class' => 'form-control-plaintext ' . $config['class_input'] . ' ' . $claseError, 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, "readonly"=>"readonly")) }}
                </div>
            </div>
            @elseif (View::exists("sirgrimorum::crudgen.templates." . $columnaT['type']))
            @include("sirgrimorum::crudgen.templates." . $columnaT['type'], ['datos'=>$columnaT,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$datos['modelo'], 'registro'=>$pivote,'errores'=>false, 'config'=>$config, 'columna'=>$columnaT['campo']])
            @else
            @include("sirgrimorum::crudgen.templates.text", ['datos'=>$columnaT,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$datos['modelo'], 'registro'=>$pivote,'errores'=>false, 'config'=>$config, 'columna'=>$columnaT['campo']])
            @endif
            @if(isset($columnaT['post_html']))
            {!! $columnaT['post_html'] !!}
            @endif
            @endif
            @if($loop->last)
            @if(!$loop->first)
        </div>
    </div>
    @endif
    <div class="collapse" id='{{ $tabla . '_' . $columna . "_" . $tablaOtroId}}_info'>
        <div class='card-footer text-muted'>
            @include("sirgrimorum::admin.show.simple", ["modelo" => class_basename($datos["modelo"]),"config" => "","registro"=>$tablaOtroId])
        </div>
    </div>
</div>
@endif
@endif
@endforeach