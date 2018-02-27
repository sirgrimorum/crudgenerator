
@foreach ($datos['columnas'] as $columnaT)
<?php
if ($columnaT['type'] == 'label') {
    $valorM = CrudLoader::getNombreDeLista($tablaInterCampo, $datos['campo']);
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
if (isset($columnaT['campo'])) {
    $atributos['class'] .= ' ' . $columna . '_' . $columnaT['campo'];
    $atributos['id'] = $columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId;
}
?>
@if ($loop->first)
<div class="card mb-1 mt-1 {{$card_class}}" id="{{$columna . "_" . $tablaOtroId}}_principal">
    {{ Form::hidden($columna. "[" . $tablaOtroId ."]", $tablaOtroId, array('class' => 'form-control', 'id' => $columna . '_' . $tablaInterId)) }}
    <div class="card-header text-center">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group" aria-label="Third group">
                    <button type="button" class="btn btn-dark" data-toggle="collapse" href="#{{ $tabla . '_' . $columna . "_" . $tablaOtroId}}_info">{{ trans("crudgenerator::admin.layout.labels.info")}}</button>
                </div>
                <h5 class="card-title mb-2 mt-2"><!--small>{{ $columnaT['label'] }}</small><br-->{{ $valorM }}</h5>
                <div class="btn-group" role="group" aria-label="Third group">
                    <button type="button" class="btn btn-danger" onclick="quitarPivote('{{$columna . "_" . $tablaOtroId}}_principal','{{ $valorM }}')" title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus fa-lg" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>

    </div>
    @if(!$loop->last)
    <div class="card-body">

        <div class="form-row">
            @endif
            @else
            @if ($columnaT['type']=='hidden')
            {{ Form::hidden($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, array('class' => 'form-control', 'id' => $columna . "_" . $columnaT['campo'] . "_" . $tablaInterId)) }}
            @else
            <div class="form-group col">
                {{ Form::label($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, ucfirst($columnaT['label']), ['class'=>""]) }}
                @if ($columnaT['type']=='label' || $columnaT['type']=='labelpivot')
                {{ $valorM }}
                @elseif ($columnaT['type']=='text')
                {{ Form::text($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, $atributos) }}
                @elseif ($columnaT['type']=='textarea')
                {{ Form::textarea($columna. "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, $atributos) }}
                @elseif ($columnaT['type']=='number')
                {{ Form::number($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, $atributos) }}
                @elseif ($columnaT['type']=='select')
                {{ Form::select($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $columnaT['opciones'], $valorM, $atributos) }}
                @else
                {{ Form::text($columna . "_" . $columnaT['campo'] . "_" . $tablaOtroId, $valorM, $atributos) }}
                @endif
            </div>
            @endif
            @endif
            @if($loop->last)
            @if(!$loop->first)
        </div>
    </div>
    @endif
    <div class="collapse" id='{{ $tabla . '_' . $columna . "_" . $tablaOtroId}}_info'>
        <div class='card-footer text-muted'>
            @include("sirgrimorum::admin.show.simple", ["modelo" => class_basename($datos["modelo"]),"config" => "","registro"=>$tablaInterId])
        </div>
    </div>
</div>
@endif
@endforeach