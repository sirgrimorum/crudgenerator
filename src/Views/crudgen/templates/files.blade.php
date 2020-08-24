<?php
$auxprevios = [];
$dato = "";
$datoImg = "";
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
} else {
    $extraId = $columna;
}
$previo = old($extraId . "_name");
if (!is_array($previo)) {
    $previo = [];
}
try {
    $auxprevios = json_decode($registro->{$columna});
    if (!is_array($auxprevios)){
        $auxprevios = [];
    }
} catch (Exception $ex) {
    $auxprevios = [];
}
if (count($previo) > 0) {
    $dato = $previo[0];
    array_shift($previo);
}
if ($dato == "") {
    if (isset($datos["valor"])) {
        $dato = $datos["valor"];
    }
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($columna . ".0") || $errors->has($columna)) {
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

<div class="form-group row {{$config['class_formgroup']}}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}" id="{{$tabla . "_" . $extraId}}_container">
        @foreach($auxprevios as $datoReg)
        @if(is_object($datoReg))
        <?php
        $filename = \Illuminate\Support\Str::start($datoReg->file, \Illuminate\Support\Str::finish($datos['path'], '\\'));
        $tipoFile =CrudGenerator::filenameIs($datoReg->file,$datos);
        $error_campo = false;
        $claseError = '';
        ?>
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary">
                    @if ($tipoFile=='image')
                    <img class="rounded-left " style="cursor: pointer;" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" onclick="toogleImagen(this);">
                    @elseif($tipoFile=='video')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleVideo(this);"><i class="mt-2 fa fa-film fa-lg" aria-hidden="true"></i></div>
                    @elseif($tipoFile=='audio')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleAudio(this);"><i class="mt-2 fa fa-file-audio-o fa-lg" aria-hidden="true"></i></div>
                    @elseif($tipoFile=='pdf')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="tooglePdf(this);"><i class="mt-2 fa fa-file-pdf-o fa-lg" aria-hidden="true"></i></div>
                    @elseif($tipoFile=='other')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-o fa-lg" aria-hidden="true"></i></div>
                    @else
                    <a class="d-block pl-3 pr-3 h-100 pt-1 text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                    @switch($tipoFile)
                    @case('video')
                    <i class="mt-2 fa fa-film fa-lg" aria-hidden="true"></i>
                    @break
                    @case('audio')
                    <i class="mt-2 fa fa-file-audio-o fa-lg" aria-hidden="true"></i>
                    @break
                    @case('pdf')
                    <i class="mt-2 fa fa-file-pdf-o fa-lg" aria-hidden="true"></i>
                    @break
                    @case('text')
                    <i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i>
                    @break
                    @case('office')
                    <i class="mt-2 fa fa-file-word-o fa-lg" aria-hidden="true"></i>
                    @break
                    @case('compressed')
                    <i class="mt-2 fa fa-file-archive-o fa-lg" aria-hidden="true"></i>
                    @break
                    @endswitch
                    </a>
                    @endif
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_namereg[]", $datoReg->name, ['class' => 'form-control nombre_file ',  'placeholder'=>trans("crudgenerator::admin.layout.labels.name"), "required"=>"required"]) }}
            {{ Form::hidden($extraId . "_filereg[]", $datoReg->file, ['class' => 'form-control ',]) }}
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'{{$tabla . "_" . $extraId}}')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        @if($tipoFile =='image')
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" data-id="collapseImage">
            </div>
        </div>
        @elseif($tipoFile =='video')
        <div class="collapse" data-id="collapseVideoCont">
            <div class="card collapse" >
                <!--iframe class="card-img-top" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" data-id="collapseVideo"></iframe-->
                <video class="card-img-top" controls preload="auto" height="300" >
                    <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="video/mp4" />
                </video>
            </div>
        </div>
        @elseif($tipoFile =='audio')
        <div class="collapse" data-id="collapseAudioCont">
            <div class="card collapse" >
                <audio class="card-img-top" controls preload="auto" >
                    <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="audio/mpeg" />
                </audio>
            </div>
        </div>
        @elseif($tipoFile =='pdf')
        <div class="collapse" data-id="collapsePdfCont">
            <div class="card collapse" >
                <iframe class="card-img-top" style="min-height: 500px;" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" style="border: none;"></iframe>
            </div>
        </div>
        @endif
        @endif
        @endforeach
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text rounded-left">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input form-control ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-success" onclick="addFile('{{$tabla . "_" . $extraId}}','{{$extraId}}')" title="{{trans("crudgenerator::admin.layout.labels.add")}}"><i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
            @if ($error_campo)
            <div class="invalid-feedback">
                @if($errors->has($columna . ".0"))
                {{ $errors->get($columna . ".0")[0] }}
                @elseif($errors->has($columna))
                {{ $errors->get($columna)[0] }}
                @endif
            </div>
            @endif
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
        @foreach($previo as $index=>$dato)
        @if($dato!="")
        <?php
        $error_campo = false;
        $claseError = '';
        if ($errores == true) {
            if ($errors->has($columna . "." . ($index + 1))) {
                $error_campo = true;
                $claseError = 'is-invalid';
            } else {
                $claseError = 'is-valid';
            }
        }
        ?>
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),"required"=>"required",$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'nuevo')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
            @if ($error_campo)
            <div class="invalid-feedback">
                {{ $errors->get($columna . "." . ($index+1))[0] }}
            </div>
            @endif
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div class="d-none" id="{{$tabla . "_" . $extraId}}_clone">
        <div class="input-group mt-2 mb-0" >
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text("nombre", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'nuevo')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
    </div>
</div>