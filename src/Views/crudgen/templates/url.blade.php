<?php
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
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
if (isset($datos["placeholder"])) {
    $placeholder = $datos['placeholder'];
} else {
    $placeholder = "";
}
?>
<div class="form-group row">
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary">
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleUrl(this);"><i class="mt-2 fa fa-link fa-lg" aria-hidden="true"></i></div>
                </div>
                @if (isset($datos["pre"]))
                <div class="input-group-text">{{ $datos["pre"] }}</div>
                @endif
            </div>
            {{ Form::text($extraId, $dato, array('class' => 'form-control ' . $config['class_input'] . ' ' . $claseError, 'id' => $tabla . '_' . $extraId, 'placeholder'=>$placeholder,$readonly)) }}
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
            {{ $errors->get($columna)[0] }}
        </div>
        @endif
    </div>
</div>
