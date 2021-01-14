<?php
if (isset($datos['extraId'])) {
    $extraId = $datos['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId);
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
if (isset($datos["placeholder"])) {
    $placeholder = $datos['placeholder'];
} else {
    $placeholder = "";
}
$extraClassDiv = \Illuminate\Support\Arr::get($datos, 'extraClassDiv', "");
$extraClassInput = \Illuminate\Support\Arr::get($datos, 'extraClassInput', "");
$extraDataInput = \Illuminate\Support\Arr::get($datos, 'extraDataInput', []);
$help = \Illuminate\Support\Arr::get($datos, 'help', "");
?>
<div class="form-group row {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        <div class="input-group {{ $claseError }}">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary">
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleUrl(this);">{!! CrudGenerator::getIcon('url',true,'mt-2') !!}</div>
                </div>
                @if (isset($datos["pre"]))
                <div class="input-group-text">{{ $datos["pre"] }}</div>
                @endif
            </div>
            {{ Form::text($extraId, $dato, array_merge(
                $extraDataInput,
                ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId, 'placeholder'=>$placeholder,$readonly])) }}
            @if (isset($datos["post"]))
            <div class="input-group-append"><div class="input-group-text">{{ $datos["post"] }}</div></div>
            @endif
        </div>
        <div class="collapse" data-id="collapseUrlCont">
            <div class="card" >
                <?php
                if (CrudGenerator::urlType($dato) == "youtube") {
                    $youtubeId = CrudGenerator::getYoutubeId($registro->{$columna});
                    $link = "https://www.youtube.com/embed/". $youtubeId;
                }else{
                    $link = $dato;
                }
                ?>
                <iframe class="card-img-top" height="400" src="{{$link}}" style="border: none;"></iframe>
            </div>
        </div>
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
